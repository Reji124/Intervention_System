<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $schoolYearId = $request->query('school_year_id');
        $semesterId   = $request->query('semester_id');

        // ── Stat chips ────────────────────────────────────────────────────────

        $totalTeachers = Teacher::count();

        $totalExamsUploaded = Exam::count();

        $newTeachersThisMonth = Teacher::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Overall pass rate across all exam results (unfiltered — always global)
        $totalResults    = ExamResult::count();
        $totalPassed     = ExamResult::where('remark', 'pass')->count();
        $overallPassRate = $totalResults > 0
            ? (int) round(($totalPassed / $totalResults) * 100)
            : 0;

        // Count distinct failing students (scoped to selected SY/semester if set)
        $failingStudents = $this->resultsQuery($schoolYearId, $semesterId)
            ->where('remark', 'fail')
            ->distinct()
            ->count('student_id');

        // ── Exam type breakdown ───────────────────────────────────────────────

        $examBreakdown = [];

        foreach (['prelim', 'midterm', 'prefinal', 'final'] as $type) {

            $query = $this->resultsQuery($schoolYearId, $semesterId)
                ->whereHas('exam', fn($q) => $q->where('exam_type', $type));

            $total = $query->count();
            $pass  = (clone $query)->where('remark', 'pass')->count();
            $fail  = $total - $pass;

            $examBreakdown[$type] = [
                'total'     => $total,
                'pass'      => $pass,
                'fail'      => $fail,
                'pass_rate' => $total > 0 ? (int) round(($pass / $total) * 100) : 0,
            ];
        }

        // ── Teacher performance table ─────────────────────────────────────────

        $teacherPerformance = Teacher::orderBy('teacher_name')->get()
            ->map(function ($teacher) use ($schoolYearId, $semesterId) {

                $subjectQuery = TeacherSubject::where('teacher_id', $teacher->id);

                if ($semesterId) {
                    $subjectQuery->where('semester_id', $semesterId);
                } elseif ($schoolYearId) {
                    $subjectQuery->whereHas('semester', fn($q) =>
                        $q->where('school_year_id', $schoolYearId)
                    );
                }

                $subjectIds = $subjectQuery->pluck('id');

                $resultsQuery = ExamResult::whereHas('exam', fn($q) =>
                    $q->whereIn('teacher_subject_id', $subjectIds)
                );

                $total = $resultsQuery->count();
                $pass  = (clone $resultsQuery)->where('remark', 'pass')->count();

                $teacher->subjects_count = $subjectIds->count();
                $teacher->exams_count    = Exam::whereIn('teacher_subject_id', $subjectIds)->count();
                // Unique students who have at least one exam result in this teacher's subjects
                $teacher->total_students = ExamResult::whereHas('exam', fn($q) =>
                    $q->whereIn('teacher_subject_id', $subjectIds)
                )->distinct()->count('student_id');
                $teacher->pass_rate      = $total > 0 ? (int) round(($pass / $total) * 100) : 0;

                return $teacher;
            });

        // Teachers at risk = pass rate below 60% (only those with actual results)
        $atRiskTeachers = $teacherPerformance
            ->filter(fn($t) => $t->exams_count > 0 && $t->pass_rate < 60)
            ->count();

        // ── Subjects at risk ──────────────────────────────────────────────────

        $subjectQuery = TeacherSubject::with(['subject', 'teacher']);

        if ($semesterId) {
            $subjectQuery->where('semester_id', $semesterId);
        } elseif ($schoolYearId) {
            $subjectQuery->whereHas('semester', fn($q) =>
                $q->where('school_year_id', $schoolYearId)
            );
        }

        $subjectsAtRisk = $subjectQuery->get()
            ->map(function ($ts) {
                $base = ExamResult::whereHas('exam', fn($q) =>
                    $q->where('teacher_subject_id', $ts->id)
                );

                $total = $base->count();
                $pass  = (clone $base)->where('remark', 'pass')->count();

                $passRate = $total > 0 ? (int) round(($pass / $total) * 100) : null;

                return (object) [
                    'subject_code' => $ts->subject->subject_code ?? '—',
                    'teacher_name' => $ts->teacher->teacher_name ?? '—',
                    'pass_rate'    => $passRate,
                    'total'        => $total,
                ];
            })
            ->filter(fn($s) => $s->total > 0 && $s->pass_rate !== null && $s->pass_rate < 60)
            ->sortBy('pass_rate')
            ->values()
            ->take(8);

        // ── Filter dropdowns ──────────────────────────────────────────────────

        $schoolYears = SchoolYear::orderByDesc('id')->get();
        $semesters   = Semester::with('schoolYear')->orderByDesc('id')->get();

        return view('admin.dashboard', compact(
            'totalTeachers',
            'totalExamsUploaded',
            'failingStudents',
            'newTeachersThisMonth',
            'overallPassRate',
            'atRiskTeachers',
            'examBreakdown',
            'teacherPerformance',
            'subjectsAtRisk',
            'schoolYears',
            'semesters',
        ));
    }

    // ── Shared base query scoped to SY + semester ─────────────────────────────

    private function resultsQuery(?int $schoolYearId, ?int $semesterId)
    {
        $query = ExamResult::query();

        if ($semesterId) {
            $query->whereHas('exam.teacherSubject', fn($q) =>
                $q->where('semester_id', $semesterId)
            );
        } elseif ($schoolYearId) {
            $query->whereHas('exam.teacherSubject.semester', fn($q) =>
                $q->where('school_year_id', $schoolYearId)
            );
        }

        return $query;
    }
}