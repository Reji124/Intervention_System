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

    // Still load teacher_subjects so JS can do cascading filtering
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

    return view('assistant.upload.index', compact(
        'schoolYears',
        'semesters',
        'subjects',
        'teachers',
        'teacherSubjects',
        'activeSemester',
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
        ]);

        // ── Master list ──────────────────────────────────────────────────────
        $masterPath = $request->file('master_list')
            ->store('temp/master_lists', 'local');

        $parser = new MasterListParser();
        $rows   = $parser->parse(storage_path("app/private/{$masterPath}"));

        Storage::disk('local')->delete($masterPath);

        if (empty($rows)) {
            return back()->withInput()
                ->with('error', 'Could not extract any student data from the PDF. Please check the file and try again.');
        }

        // ── Enrich rows with DB name-match info ──────────────────────────────
        $existingStudents = Student::whereIn(
            'student_code',
            collect($rows)->pluck('student_code')->filter()->unique()->values()->toArray()
        )->get()->keyBy('student_code');

        foreach ($rows as &$row) {
            $code    = trim($row['student_code'] ?? '');
            $pdfName = trim($row['student_name'] ?? '');

            if (!empty($code) && $existing = $existingStudents->get($code)) {
                $dbName = trim($existing->student_name);

                if (strtolower($dbName) !== strtolower($pdfName)) {
                    $row['flagged']  = true;
                    $row['db_name']  = $dbName;
                    $row['mismatch'] = true;
                } else {
                    $row['db_name']  = null;
                    $row['mismatch'] = false;
                }
            } else {
                $row['db_name']  = null;
                $row['mismatch'] = false;
            }
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
        ]);

        // Pull session data BEFORE the transaction
        $parsed     = session('item_matrix_parsed');
        $matrixJson = $parsed ? $this->buildMatrixJson($parsed) : null;
        session()->forget('item_matrix_parsed');

        $saved      = 0;
        $skipped    = 0;
        $uploaderId = Auth::id(); // ← capture the assistant's user ID

        DB::transaction(function () use ($request, $matrixJson, $uploaderId, &$saved, &$skipped) {

            $exam = Exam::firstOrCreate(
                [
                    'teacher_subject_id' => $request->teacher_subject_id,
                    'exam_type'          => $request->exam_type,
                ],
                [
                    'item_analysis_path' => null,
                    'item_matrix_data'   => $matrixJson,
                    'uploaded_by'        => $uploaderId, // ← stamp on first create
                ]
            );

            // Always overwrite matrix data and uploader if we have new parsed data
            $updatePayload = ['uploaded_by' => $uploaderId];
            if ($matrixJson) {
                $updatePayload['item_matrix_data'] = $matrixJson;
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