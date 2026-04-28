<?php

// app/Http/Controllers/Assistant/PdfUploadController.php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Semester;
use App\Models\Student;
use App\Models\TeacherSubject;
use App\Services\ItemMatrixParser;
use App\Services\MasterListParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PdfUploadController extends Controller
{
    // ── Step 1: Show upload form ──────────────────────────────────────────────
    public function index()
    {
        $schoolYears = \App\Models\SchoolYear::with('semesters')
            ->orderByDesc('year_start')
            ->get();

        $semesters = \App\Models\Semester::with('schoolYear')
            ->orderByDesc('id')
            ->get();

        $subjects = \App\Models\Subject::orderBy('subject_code')->get();

        $teachers = \App\Models\Teacher::orderBy('teacher_name')->get();

        $teacherSubjects = TeacherSubject::with([
                'subject',
                'teacher',
                'semester.schoolYear',
            ])
            ->orderBy('teacher_id')
            ->get();

        $activeSemester = Semester::with('schoolYear')
            ->where('is_active', true)->first()
            ?? Semester::with('schoolYear')->latest('id')->first();

        $tsJson = $teacherSubjects->map(function ($ts) {
            return [
                'id'           => $ts->id,
                'semester_id'  => $ts->semester_id,
                'subject_id'   => $ts->subject_id,
                'teacher_id'   => $ts->teacher->id,
                'teacher_name' => $ts->teacher->teacher_name,
                'subject_code' => $ts->subject->subject_code,
                'subject_name' => $ts->subject->subject_name,
                'section'      => $ts->section,
                'semester_name'=> $ts->semester->semester_name,
            ];
        })->values();

        // ── Build locked exam map ────────────────────────────────────────────
        // Locked = exam has at least one ExamResult (master list uploaded)
        //          AND item_matrix_data is not null (matrix uploaded).
        // Shape: { "<teacher_subject_id>": ["prelim", "midterm", ...] }
        $tsIds = $teacherSubjects->pluck('id')->toArray();

        $examRows = Exam::whereIn('teacher_subject_id', $tsIds)
            ->withCount('examResults')
            ->get();

        $lockedExams = [];
        foreach ($examRows as $exam) {
            $hasMasterList = $exam->exam_results_count > 0;
            $hasItemMatrix = !empty($exam->item_matrix_data);

            if ($hasMasterList && $hasItemMatrix) {
                $lockedExams[$exam->teacher_subject_id][] = $exam->exam_type;
            }
        }

        return view('assistant.upload.index', compact(
            'schoolYears',
            'semesters',
            'subjects',
            'teachers',
            'teacherSubjects',
            'tsJson',
            'activeSemester',
            'lockedExams',
        ));
    }

    // ── Step 2: Parse PDFs → show review ─────────────────────────────────────
    public function parse(Request $request)
    {
        $request->validate([
            'teacher_subject_id' => 'required|exists:teacher_subjects,id',
            'exam_type'          => 'required|in:prelim,midterm,prefinal,final',
            'master_list'        => 'required|file|mimes:pdf|max:10240',
            'item_matrix'        => 'nullable|file|mimes:pdf|max:10240',
            'grading_method'     => 'required|in:base_50,base_20',
        ]);

        // ── Guard: reject if already fully locked ────────────────────────────
        $existingExam = Exam::where('teacher_subject_id', $request->teacher_subject_id)
            ->where('exam_type', $request->exam_type)
            ->withCount('examResults')
            ->first();

        if (
            $existingExam &&
            $existingExam->exam_results_count > 0 &&
            !empty($existingExam->item_matrix_data)
        ) {
            return back()->withInput()
                ->with('error', 'This exam already has both a master list and item matrix uploaded. It cannot be re-uploaded.');
        }

        // ── Master list ──────────────────────────────────────────────────────
        $masterPath = $request->file('master_list')
            ->store('temp/master_lists', 'local');

        $parser = new MasterListParser();
        $rows = $parser->parse(storage_path("app/private/{$masterPath}"));
        Storage::disk('local')->delete($masterPath);

        if (empty($rows)) {
            return back()->withInput()
                ->with('error', 'Could not extract any student data from the PDF. Please check the file and try again.');
        }

        // ── Recalculate percentage + remark using selected grading method ────────
        $gradingMethod = $request->grading_method;

        foreach ($rows as &$row) {
            $score = (int)   ($row['raw_score']   ?? 0);
            $total = (int)   ($row['total_items'] ?? 0);

            if ($total > 0 && $score >= 0) {
                $row['percentage'] = match($gradingMethod) {
                    'base_20' => round(20 + ($score / $total * 80), 2),
                    default   => round(50 + ($score / $total * 50), 2),
                };
                $row['remark'] = $row['percentage'] >= 75.0 ? 'pass' : 'fail';
            }
            // If total could not be derived, keep the PDF grade as fallback
        }
        unset($row);

        // ── Item matrix (optional) ───────────────────────────────────────────
        $matrixData = null;

        if ($request->hasFile('item_matrix')) {
            $matrixPath = $request->file('item_matrix')
                ->store('temp/item_matrices', 'local');

            $matrixParser = new ItemMatrixParser();
            $matrixData   = $matrixParser->parse(
                storage_path("app/private/{$matrixPath}")
            );

            Storage::disk('local')->delete($matrixPath);

            session(['item_matrix_parsed' => $matrixData]);
        } else {
            session()->forget('item_matrix_parsed');
        }

        // ── Context ──────────────────────────────────────────────────────────
        $ts = TeacherSubject::with(['subject', 'teacher', 'semester.schoolYear'])
            ->findOrFail($request->teacher_subject_id);

        $context = [
            'teacher_subject_id' => $ts->id,
            'exam_type'          => $request->exam_type,
            'grading_method'     => $request->grading_method,
            'item_matrix_path'   => null,
            'subject_code'       => $ts->subject->subject_code,
            'subject_name'       => $ts->subject->subject_name,
            'section'            => $ts->section,
            'teacher_name'       => $ts->teacher->teacher_name,
            'semester'           => $ts->semester->semester_name . ' Sem, S.Y. '
                                    . $ts->semester->schoolYear->year_start . '–'
                                    . $ts->semester->schoolYear->year_end,
        ];

        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.upload.review', compact(
            'rows', 'context', 'matrixData', 'activeSemester'
        ));
    }

    // ── Step 3: Save confirmed results ───────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'teacher_subject_id'      => 'required|exists:teacher_subjects,id',
            'exam_type'               => 'required|in:prelim,midterm,prefinal,final',
            'students'                => 'required|array',
            'students.*.student_name' => 'nullable|string|max:255',
            'students.*.student_code' => 'nullable|string|max:50',
            'students.*.raw_score'    => 'required|integer',
            'students.*.percentage'   => 'required|numeric',
            'students.*.remark'       => 'required|in:pass,fail',
            'grading_method'          => 'required|in:base_50,base_20',
        ]);

        // Pull session data BEFORE the transaction
    $editedJson = $request->input('item_matrix_edited_json');
    $matrixJson = $editedJson ? json_decode($editedJson, true) : null;

        $saved      = 0;
        $skipped    = 0;
        $uploaderId = Auth::id();

        DB::transaction(function () use ($request, $matrixJson, $uploaderId, &$saved, &$skipped) {

            $exam = Exam::firstOrCreate(
                [
                    'teacher_subject_id' => $request->teacher_subject_id,
                    'exam_type'          => $request->exam_type,
                    'grading_method'     => $request->grading_method,
                ],
                [
                    'item_analysis_path' => null,
                    'item_matrix_data'   => $matrixJson,
                    'uploaded_by'        => $uploaderId,
                ]
            );

            // Always overwrite matrix data and uploader if we have new parsed data
            $updatePayload = ['uploaded_by' => $uploaderId];
            if ($matrixJson) {
                $updatePayload['item_matrix_data'] = $matrixJson;
                $updatePayload['grading_method'] = $request->grading_method;
            }
            $exam->update($updatePayload);

            foreach ($request->students as $row) {
                $name = trim($row['student_name'] ?? '');
                $code = trim($row['student_code'] ?? '');

                if (empty($name) || empty($code)) {
                    $skipped++;
                    continue;
                }

                $student = Student::firstOrCreate(
                    ['student_code' => $code],
                    [
                        'student_name'       => $name,
                        'teacher_subject_id' => $request->teacher_subject_id,
                    ]
                );

                if (strtolower(trim($student->student_name)) !== strtolower($name)) {
                    $student->update(['student_name' => $name]);
                }

                $exists = ExamResult::where('student_id', $student->id)
                    ->where('exam_id', $exam->id)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                ExamResult::create([
                    'student_id' => $student->id,
                    'exam_id'    => $exam->id,
                    'raw_score'  => $row['raw_score'],
                    'percentage' => $row['percentage'],
                    'remark'     => $row['remark'],
                ]);

                $saved++;
            }
        });

        session()->flash('success',
            "Saved {$saved} student result(s)." .
            ($skipped > 0 ? " {$skipped} skipped (already exist or missing info)." : '')
        );

        return redirect()->route('assistant.dashboard');
    }

    // ── Convert ItemMatrixParser output → JSON shape for intervention blades ──
    private function buildMatrixJson(?array $parsed): ?array
    {
        if (!$parsed || empty($parsed['total_items'])) {
            return null;
        }

        $discCols  = \App\Services\ItemMatrixParser::DISCRIMINATION_COLS;
        $diffBands = \App\Services\ItemMatrixParser::DIFFICULTY_BANDS;

        $rows = [];
        foreach ($diffBands as $band => $label) {
            $rows[] = [
                'difficulty' => $band,
                'label'      => $label,
                'columns'    => $parsed['cells'][$band] ?? array_fill_keys($discCols, []),
                'total'      => $parsed['row_totals'][$band] ?? 0,
            ];
        }

        return [
            'title'         => $parsed['title']      ?? '',
            'module'        => $parsed['module']      ?? '',
            'date'          => $parsed['date']        ?? '',
            'disc_columns'  => $discCols,
            'rows'          => $rows,
            'column_totals' => $parsed['col_totals']  ?? array_fill_keys($discCols, 0),
            'grand_total'   => $parsed['total_items'] ?? 0,
            'legend'        => [
                'reject'         => $parsed['legend']['reject']         ?? [],
                'needs_revision' => $parsed['legend']['needs_revision'] ?? [],
                'acceptable'     => $parsed['legend']['acceptable']     ?? [],
            ],
        ];
    }
}