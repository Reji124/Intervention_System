<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        // ── Safe defaults ──────────────────────────────────────────────────────
        $schoolYears    = collect();
        $semesters      = collect();
        $departments    = collect();
        $subjects       = collect();
        $teachers       = collect();
        $categories     = collect();
        $grouped        = collect();   // mirrors assistant: teacher → subjects
        $activeSemester = null;
        $totalFailing   = 0;
        $totalPassing   = 0;

        $selectedSY      = $request->input('school_year_id');
        $selectedSem     = $request->input('semester_id');
        $selectedDept    = $request->input('department_id');
        $selectedCat     = $request->input('category');
        $selectedSubject = $request->input('subject_id');
        $selectedTeacher = $request->input('teacher_id');

        // filled() treats empty string as absent; has() does not.
        $isFilteredRequest = $request->filled('_filtered');

        try {
            // ── Dropdown data ──────────────────────────────────────────────────
            $schoolYears = SchoolYear::with('semesters')->orderByDesc('year_start')->get();
            $semesters   = Semester::with('schoolYear')->orderByDesc('id')->get();
            $departments = Department::orderBy('department_name')->get();
            $subjects    = Subject::with('department')->orderBy('subject_code')->get();
            $teachers    = Teacher::with(['teacherSubjects.subject', 'teacherSubjects.semester'])
                                  ->orderBy('teacher_name')
                                  ->get();

            $categories = DB::table('subjects')
                            ->select(DB::raw('DISTINCT category'))
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->orderBy('category')
                            ->pluck('category');

            // Replace the existing activeSemester block with this:
            $activeSemester = Semester::with('schoolYear')
                    ->where('is_active', true)
                    ->first()
                 ?? Semester::with('schoolYear')
                    ->latest('id')
                    ->first();

            // Apply default semester only on a genuine first load.
            if (!$isFilteredRequest && !$selectedSem && !$selectedSY) {
                $selectedSem = $activeSemester?->id;
            }

            // ── Main query — same strategy as assistant controller ─────────────
            // Query from TeacherSubject outward (not from Exam inward).
            // This guarantees every subject appears even if it has no exams,
            // and it avoids the semester-mismatch issue the previous approach had.
            $tsQuery = TeacherSubject::with([
                    'teacher',
                    'subject.department',
                    'semester.schoolYear',
                    'exams.examResults.student',
                    'exams.examResults.exam',
                ])
                ->whereHas('teacher')   // skip orphaned rows
                ->whereHas('subject');

            // ── Apply filters ──────────────────────────────────────────────────
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

            // PostgreSQL: case-insensitive category match
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

            // ── Build grouped structure (identical to assistant) ───────────────
            $grouped = $teacherSubjects
                ->filter(fn($ts) => $ts->teacher && $ts->subject)
                ->groupBy(fn($ts) => $ts->teacher->teacher_name)
                ->map(fn($teacherTSList) =>
                    $teacherTSList->mapWithKeys(function ($ts) {
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
                            'label'           => $label,
                            'all_results'     => $allResults,
                            'failing_results' => $failingResults,
                            'pass_count'      => $allResults->where('remark', 'pass')->count(),
                            'fail_count'      => $failingResults->count(),
                            'total_count'     => $allResults->count(),
                            'exam'            => $examWithMatrix ?? $anyExam,
                        ]];
                    })
                );

            // ── Summary totals scoped to current filter ────────────────────────
            // Collect all exam IDs from the filtered teacher-subjects.
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

    public function destroyExam(\App\Models\Exam $exam)
    {
        $exam->examResults()->delete();
        $exam->delete();
        return response()->json(['success' => true]);
    }
}