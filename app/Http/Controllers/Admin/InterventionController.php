<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherNote;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterventionController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $schoolYears    = collect();
        $semesters      = collect();
        $departments    = collect();
        $subjects       = collect();
        $teachers       = collect();
        $categories     = collect();
        $grouped        = collect();
        $activeSemester = null;
        $totalFailing   = 0;
        $totalPassing   = 0;

        $selectedSY      = $request->input('school_year_id');
        $selectedSem     = $request->input('semester_id');
        $selectedDept    = $request->input('department_id');
        $selectedCat     = $request->input('category');
        $selectedSubject = $request->input('subject_id');
        $selectedTeacher = $request->input('teacher_id');

        $isFilteredRequest = $request->filled('_filtered');

        try {
            $schoolYears = SchoolYear::with('semesters')->orderByDesc('year_start')->get();
            $semesters   = Semester::with('schoolYear')->orderByDesc('id')->get();
            $departments = Department::orderBy('department_name')->get();
            $subjects    = Subject::with('department')->orderBy('subject_code')->get();
            $teachers    = Teacher::with([
                                'teacherSubjects.subject',
                                'teacherSubjects.semester',
                                'notes',
                            ])
                                  ->orderBy('teacher_name')
                                  ->get();

            $categories = DB::table('subjects')
                            ->select(DB::raw('DISTINCT category'))
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->orderBy('category')
                            ->pluck('category');

            $activeSemester = Semester::with('schoolYear')
                    ->where('is_active', true)
                    ->first()
                 ?? Semester::with('schoolYear')
                    ->latest('id')
                    ->first();

            if (!$isFilteredRequest && !$selectedSem && !$selectedSY) {
                $selectedSem = $activeSemester?->id;
            }

            // ── Main query ────────────────────────────────────────────────────
            $tsQuery = TeacherSubject::with([
                    'teacher',
                    'subject.department',
                    'semester.schoolYear',
                    'exams.examResults.student',
                    'exams.examResults.exam',
                    'exams.uploadedBy',   // ← eager-load the uploader
                ])
                ->whereHas('teacher')
                ->whereHas('subject');

            if ($selectedSem) {
                $tsQuery->where('semester_id', $selectedSem);
            } elseif ($selectedSY) {
                $tsQuery->whereHas('semester', fn($q) =>
                    $q->where('school_year_id', $selectedSY));
            }

            if ($selectedDept) {
                $tsQuery->whereHas('subject', fn($q) =>
                    $q->where('department_id', $selectedDept));
            }

            if ($selectedCat) {
                $tsQuery->whereHas('subject', fn($q) =>
                    $q->whereRaw('LOWER(category) = LOWER(?)', [$selectedCat]));
            }

            if ($selectedSubject) {
                $tsQuery->where('subject_id', $selectedSubject);
            }

            if ($selectedTeacher) {
                $tsQuery->where('teacher_id', $selectedTeacher);
            }

            $teacherSubjects = $tsQuery->orderBy('teacher_id')->get();

            // ── Load teacher notes for active semester ─────────────────────────
            $notesSemesterId = $selectedSem ?? $activeSemester?->id;
            $teacherNotes    = TeacherNote::where('semester_id', $notesSemesterId)
                ->get()
                ->keyBy('teacher_id');

            // ── Build grouped structure ───────────────────────────────────────
            $grouped = $teacherSubjects
                ->filter(fn($ts) => $ts->teacher && $ts->subject)
                ->groupBy(fn($ts) => $ts->teacher->teacher_name)
                ->map(function ($teacherTSList) use ($teacherNotes) {
                    // Attach note to the teacher model for blade use
                    $teacher     = $teacherTSList->first()->teacher;
                    $teacherNote = $teacherNotes->get($teacher->id);

                    return $teacherTSList->mapWithKeys(function ($ts) use ($teacherNote) {
                        $allResults     = $ts->exams->flatMap(fn($e) => $e->examResults);
                        $failingResults = $allResults->where('remark', 'fail')
                                                     ->sortBy('percentage')
                                                     ->values();

                        $examWithMatrix = $ts->exams->first(fn($e) => !empty($e->item_matrix_data));
                        $anyExam        = $ts->exams->first();

                        $label = $ts->subject->subject_code
                               . ' — ' . $ts->subject->subject_name
                               . ($ts->section ? ' (' . $ts->section . ')' : '');

                        return [$label => [
                            'teacher_subject' => $ts,
                            'teacher_note'    => $teacherNote,   // ← attached to every subject row
                            'label'           => $label,
                            'all_results'     => $allResults,
                            'failing_results' => $failingResults,
                            'pass_count'      => $allResults->where('remark', 'pass')->count(),
                            'fail_count'      => $failingResults->count(),
                            'total_count'     => $allResults->count(),
                            'exam'            => $examWithMatrix ?? $anyExam,
                        ]];
                    });
                });

            $examIds      = $teacherSubjects->flatMap(fn($ts) => $ts->exams->pluck('id'));
            $totalFailing = $examIds->isNotEmpty()
                ? ExamResult::whereIn('exam_id', $examIds)->where('remark', 'fail')->count()
                : 0;
            $totalPassing = $examIds->isNotEmpty()
                ? ExamResult::whereIn('exam_id', $examIds)->where('remark', 'pass')->count()
                : 0;

        } catch (\Exception $e) {
            Log::error('Admin\InterventionController@index: ' . $e->getMessage(), [
                'filters' => $request->only([
                    'school_year_id', 'semester_id', 'department_id',
                    'category', 'subject_id', 'teacher_id',
                ]),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return view('admin.interventions.index', compact(
            'schoolYears', 'semesters', 'departments', 'categories',
            'subjects', 'teachers', 'grouped',
            'totalFailing', 'totalPassing', 'activeSemester',
            'selectedSY', 'selectedSem', 'selectedDept',
            'selectedCat', 'selectedSubject', 'selectedTeacher',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXAM RESULT CRUD
    // ══════════════════════════════════════════════════════════════════════════

    public function updateResult(Request $request, ExamResult $examResult)
    {
        $request->validate([
            'raw_score' => 'required|integer|min:0',
            'total'     => 'required|integer|min:1',
        ]);

        $rawScore   = (int) $request->raw_score;
        $total      = (int) $request->total;
        $percentage = round(($rawScore / $total) * 100, 2);
        $remark     = $percentage >= 75.0 ? 'pass' : 'fail';

        $examResult->update([
            'raw_score'  => $rawScore,
            'percentage' => $percentage,
            'remark'     => $remark,
        ]);

        return response()->json([
            'success'    => true,
            'raw_score'  => $rawScore,
            'percentage' => $percentage,
            'remark'     => $remark,
        ]);
    }

    public function destroyResult(ExamResult $examResult)
    {
        $examResult->delete();
        return response()->json(['success' => true]);
    }

    public function destroyExam(Exam $exam)
    {
        $exam->examResults()->delete();
        $exam->delete();
        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TEACHER NOTES
    // ══════════════════════════════════════════════════════════════════════════

    public function upsertNote(Request $request, Teacher $teacher)
    {
        $request->validate([
            'semester_id' => 'nullable|exists:semesters,id',
            'status'      => 'required|in:no_status,on_track,needs_followup,intervention_active,resolved',
            'notes'       => 'nullable|string|max:5000',
        ]);

        $note = TeacherNote::updateOrCreate(
            [
                'teacher_id'  => $teacher->id,
                'semester_id' => $request->semester_id ?: null,
            ],
            [
                'status'     => $request->status,
                'notes'      => $request->notes,
                'updated_by' => Auth::id(),
            ]
        );

        return response()->json([
            'success'      => true,
            'status'       => $note->status,
            'status_label' => $note->status_label,
            'notes'        => $note->notes,
            'updated_by'   => $note->updatedByUser?->name ?? 'Admin',
            'updated_at'   => $note->updated_at->format('M d, Y g:i A'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MASS DELETE (scoped to current filters)
    // ══════════════════════════════════════════════════════════════════════════

    public function massDelete(Request $request)
    {
        $request->validate([
            'semester_id'   => 'nullable|exists:semesters,id',
            'school_year_id'=> 'nullable|exists:school_years,id',
            'department_id' => 'nullable|exists:departments,id',
            'category'      => 'nullable|string',
            'subject_id'    => 'nullable|exists:subjects,id',
            'teacher_id'    => 'nullable|exists:teachers,id',
        ]);

        try {
            $tsQuery = TeacherSubject::query();

            if ($request->filled('semester_id')) {
                $tsQuery->where('semester_id', $request->semester_id);
            } elseif ($request->filled('school_year_id')) {
                $tsQuery->whereHas('semester', fn($q) =>
                    $q->where('school_year_id', $request->school_year_id));
            }

            if ($request->filled('department_id')) {
                $tsQuery->whereHas('subject', fn($q) =>
                    $q->where('department_id', $request->department_id));
            }

            if ($request->filled('category')) {
                $tsQuery->whereHas('subject', fn($q) =>
                    $q->whereRaw('LOWER(category) = LOWER(?)', [$request->category]));
            }

            if ($request->filled('subject_id')) {
                $tsQuery->where('subject_id', $request->subject_id);
            }

            if ($request->filled('teacher_id')) {
                $tsQuery->where('teacher_id', $request->teacher_id);
            }

            $tsIds   = $tsQuery->pluck('id');
            $examIds = Exam::whereIn('teacher_subject_id', $tsIds)->pluck('id');

            $deletedResults = ExamResult::whereIn('exam_id', $examIds)->count();

            DB::transaction(function () use ($examIds) {
                ExamResult::whereIn('exam_id', $examIds)->delete();
                Exam::whereIn('id', $examIds)->delete();
            });

            return response()->json([
                'success' => true,
                'deleted_exams'   => $examIds->count(),
                'deleted_results' => $deletedResults,
            ]);

        } catch (\Exception $e) {
            Log::error('Admin\InterventionController@massDelete: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPORT CSV (scoped to current filters)
    // ══════════════════════════════════════════════════════════════════════════

    public function exportCsv(Request $request)
    {
        $tsQuery = TeacherSubject::with([
                'teacher',
                'subject',
                'semester.schoolYear',
                'exams.examResults.student',
                'exams.uploadedBy',
            ])
            ->whereHas('teacher')
            ->whereHas('subject');

        if ($request->filled('semester_id')) {
            $tsQuery->where('semester_id', $request->semester_id);
        } elseif ($request->filled('school_year_id')) {
            $tsQuery->whereHas('semester', fn($q) =>
                $q->where('school_year_id', $request->school_year_id));
        }

        if ($request->filled('department_id')) {
            $tsQuery->whereHas('subject', fn($q) =>
                $q->where('department_id', $request->department_id));
        }

        if ($request->filled('category')) {
            $tsQuery->whereHas('subject', fn($q) =>
                $q->whereRaw('LOWER(category) = LOWER(?)', [$request->category]));
        }

        if ($request->filled('subject_id')) {
            $tsQuery->where('subject_id', $request->subject_id);
        }

        if ($request->filled('teacher_id')) {
            $tsQuery->where('teacher_id', $request->teacher_id);
        }

        $teacherSubjects = $tsQuery->get();

        $filename = 'intervention_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($teacherSubjects) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Teacher',
                'Subject Code',
                'Subject Name',
                'Section',
                'Exam Type',
                'Semester',
                'School Year',
                'Student Name',
                'Student Code',
                'Raw Score',
                'Percentage',
                'Remark',
                'Uploaded By',
            ]);

            foreach ($teacherSubjects as $ts) {
                foreach ($ts->exams as $exam) {
                    foreach ($exam->examResults as $result) {
                        if (!$result->student) continue;
                        fputcsv($handle, [
                            $ts->teacher->teacher_name ?? '—',
                            $ts->subject->subject_code ?? '—',
                            $ts->subject->subject_name ?? '—',
                            $ts->section               ?? '—',
                            ucfirst($exam->exam_type)  ?? '—',
                            $ts->semester->semester_name        ?? '—',
                            ($ts->semester->schoolYear->year_start ?? '?')
                                . '–'
                                . ($ts->semester->schoolYear->year_end ?? '?'),
                            $result->student->student_name ?? '—',
                            $result->student->student_code ?? '—',
                            $result->raw_score,
                            $result->percentage . '%',
                            ucfirst($result->remark),
                            $exam->uploadedBy->name ?? '—',
                        ]);
                    }
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}