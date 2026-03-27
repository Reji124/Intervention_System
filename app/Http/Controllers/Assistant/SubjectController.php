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
                'exams',                   // no examResults here — we'll aggregate below
            ])
            ->latest()
            ->get();

        // For each TeacherSubject, count distinct students via live exam results
        // (respects deleted exams — only counts what currently exists)
        $tsIds = $teacherSubjects->pluck('id');

        $studentCounts = \App\Models\ExamResult::query()
            ->join('exams', 'exam_results.exam_id', '=', 'exams.id')
            ->join('students', 'exam_results.student_id', '=', 'students.id')
            ->whereIn('exams.teacher_subject_id', $tsIds)
            ->select('exams.teacher_subject_id',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT students.student_code) as student_count'))
            ->groupBy('exams.teacher_subject_id')
            ->pluck('student_count', 'teacher_subject_id');

        $grouped        = $teacherSubjects->groupBy('teacher.teacher_name');
        $teachers       = Teacher::orderBy('teacher_name')->get();
        $courses        = Course::orderBy('course_name')->get();
        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.subjects.index', compact(
            'grouped',
            'teachers',
            'courses',
            'activeSemester',
            'studentCounts',   // keyed by teacher_subject_id
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