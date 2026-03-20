<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\TeacherSubject;

class SubjectController extends Controller
{
    public function index()
    {
        $teacherSubjects = TeacherSubject::with([
                'subject.department',
                'subject.course',
                'teacher',
                'semester.schoolYear',
                'students',
                'exams',
            ])
            ->latest()
            ->get();

        $grouped        = $teacherSubjects->groupBy('teacher.teacher_name');
        $teachers       = Teacher::orderBy('teacher_name')->get();
        $courses        = Course::orderBy('course_name')->get();
        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.subjects.index', compact(
            'grouped',
            'teachers',
            'courses',
            'activeSemester',
        ));
    }

    public function show(TeacherSubject $teacherSubject)
    {
        $teacherSubject->load([
            'subject.department',
            'subject.course',
            'teacher',
            'semester.schoolYear',
            'exams.examResults.student',
        ]);

        $resultsByExam = $teacherSubject->exams->mapWithKeys(function ($exam) {
            return [
                $exam->exam_type => [
                    'exam'    => $exam,
                    'results' => $exam->examResults->sortBy('student.student_name'),
                    'pass'    => $exam->examResults->where('remark', 'pass')->count(),
                    'fail'    => $exam->examResults->where('remark', 'fail')->count(),
                ],
            ];
        });

        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.subjects.show', compact(
            'teacherSubject',
            'resultsByExam',
            'activeSemester',
        ));
    }
}