<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@school.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Maria Assistant',
            'email'    => 'assistant@school.com',
            'password' => bcrypt('password'),
            'role'     => 'assistant',
        ]);

        $teacher = Teacher::create([
            'teacher_name' => 'Juan dela Cruz',
        ]);

        $dept = Department::create(['department_name' => 'BSIT']);

        $course = Course::create([
            'department_id' => $dept->id,
            'course_name'   => 'Bachelor of Science in Information Technology',
        ]);

        $subject = Subject::create([
            'department_id' => $dept->id,
            'course_id'     => $course->id,
            'subject_code'  => 'IT101',
            'subject_name'  => 'Programming 1',
            'year_level'    => '1st year',
            'category'      => 'Major',
        ]);

        $schoolYear = SchoolYear::create([
            'year_start' => 2025,
            'year_end'   => 2026,
        ]);

        $semester = Semester::create([
            'school_year_id' => $schoolYear->id,
            'semester_name'  => '2nd',
            'is_active'      => true,
        ]);

        $ts = TeacherSubject::create([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $subject->id,
            'semester_id' => $semester->id,
            'section'     => 'BSIT 1-A',
        ]);

        $exam = Exam::create([
            'teacher_subject_id' => $ts->id,
            'exam_type'          => 'prelim',
        ]);

        $student = Student::create([
            'teacher_subject_id' => $ts->id,
            'student_name'       => 'Maria Santos',
            'student_code'       => '2024-0001',
        ]);

        ExamResult::create([
            'student_id' => $student->id,
            'exam_id'    => $exam->id,
            'raw_score'  => 35,
            'percentage' => 70.00,
            'remark'     => 'fail',
        ]);
    }
}