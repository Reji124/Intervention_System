<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\TeacherSubject;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $semesterId = $request->query('semester_id');

        // ── Stat chips ────────────────────────────────────────────────────────

        $totalTeachers = Teacher::count();

        $totalExamsUploaded = Exam::count();

        $failingStudents = ExamResult::where('remark', 'fail')
            ->distinct()
            ->count('student_id');

        $newTeachersThisMonth = Teacher::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Overall pass rate across all exam results
        $totalResults = ExamResult::count();
        $totalPassed  = ExamResult::where('remark', 'pass')->count();
        $overallPassRate = $totalResults > 0
            ? (int) round(($totalPassed / $totalResults) * 100)
            : 0;

        // ── Exam type breakdown ───────────────────────────────────────────────

        $examBreakdown = [];
        foreach (['prelim', 'midterm', 'final'] as $type) {
            $query = ExamResult::whereHas('exam', fn($q) => $q->where('exam_type', $type));

            if ($semesterId) {
                $query->whereHas('exam.teacherSubject', fn($q) =>
                    $q->where('semester_id', $semesterId)
                );
            }

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

        $teacherPerformance = Teacher::all()->map(function ($teacher) use ($semesterId) {

            // Scope to semester if selected
            $subjectQuery = TeacherSubject::where('teacher_id', $teacher->id);
            if ($semesterId) {
                $subjectQuery->where('semester_id', $semesterId);
            }
            $subjectIds = $subjectQuery->pluck('id');

            $resultsQuery = ExamResult::whereHas('exam', fn($q) =>
                $q->whereIn('teacher_subject_id', $subjectIds)
            );

            $total = $resultsQuery->count();
            $pass  = (clone $resultsQuery)->where('remark', 'pass')->count();

            $teacher->subjects_count   = $subjectIds->count();
            $teacher->total_students   = Student::whereIn('teacher_subject_id', $subjectIds)->count();
            $teacher->exams_count      = Exam::whereIn('teacher_subject_id', $subjectIds)->count();
            $teacher->pass_rate        = $total > 0 ? (int) round(($pass / $total) * 100) : 0;

            return $teacher;
        })
        ->sortBy('teacher_name')
        ->values();

        // Teachers at risk = pass rate below 60%
        $atRiskTeachers = $teacherPerformance->where('pass_rate', '<', 60)->count();

        // ── Subjects at risk ──────────────────────────────────────────────────

        $subjectQuery = TeacherSubject::with(['subject', 'teacher']);
        if ($semesterId) {
            $subjectQuery->where('semester_id', $semesterId);
        }

        $subjectsAtRisk = $subjectQuery->get()
            ->map(function ($ts) {
                $total = ExamResult::whereHas('exam', fn($q) =>
                    $q->where('teacher_subject_id', $ts->id)
                )->count();

                $pass = ExamResult::where('remark', 'pass')
                    ->whereHas('exam', fn($q) =>
                        $q->where('teacher_subject_id', $ts->id)
                    )->count();

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
            ->take(8); // cap to keep the card compact

        // ── Semester list for filter dropdown ─────────────────────────────────

        $semesters = Semester::with('schoolYear')->latest()->get();

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
            'semesters',
        ));
    }
}