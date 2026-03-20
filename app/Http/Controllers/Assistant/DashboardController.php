<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTeachers   = Teacher::count();
        $totalStudents   = Student::count();
        $failingStudents = ExamResult::where('remark', 'fail')
                            ->distinct()
                            ->count('student_id');

        // Filter out any results with broken relationships
        $recentResults = ExamResult::with([
                'student',
                'exam.teacherSubject.subject',
                'exam.teacherSubject.teacher',
            ])
            ->latest()
            ->take(20)
            ->get()
            ->filter(function ($r) {
                return $r->student
                    && $r->exam
                    && $r->exam->teacherSubject
                    && $r->exam->teacherSubject->subject
                    && $r->exam->teacherSubject->teacher;
            })
            ->take(8);

        $teachers = Teacher::withCount('teacherSubjects')
            ->get()
            ->map(function ($teacher) {
                $teacher->failing_count = ExamResult::where('remark', 'fail')
                    ->whereHas('exam.teacherSubject', fn($q) =>
                        $q->where('teacher_id', $teacher->id)
                    )
                    ->distinct()
                    ->count('student_id');
                return $teacher;
            });

        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.dashboard', compact(
            'totalTeachers',
            'totalStudents',
            'failingStudents',
            'recentResults',
            'teachers',
            'activeSemester',
        ));
    }
}