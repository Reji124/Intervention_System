<?php
// database/seeders/DatabaseSeeder.php

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
        // Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@hcdc.edu.ph'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'assistant@hcdc.edu.ph'],
            ['name' => 'Maria Assistant', 'password' => bcrypt('password'), 'role' => 'assistant']
        );

        // Department + Course
        $dept = Department::firstOrCreate(['department_name' => 'School of Information Technology']);

        $course = Course::firstOrCreate([
            'department_id' => $dept->id,
            'course_name'   => 'Bachelor of Science in Information Technology',
        ]);

        // Subject (no dept/course on the model anymore)
        $subject = Subject::firstOrCreate(
            ['subject_code' => 'IT101'],
            ['subject_name' => 'Programming 1', 'year_level' => '1', 'category' => 'Major']
        );

        // Attach subject to dept+course via pivot
        $subject->courses()->syncWithoutDetaching([
            $course->id => ['department_id' => $dept->id]
        ]);

        // Teacher
        $teacher = Teacher::firstOrCreate(['teacher_name' => 'Juan dela Cruz']);

        // School Year + Semester
        $schoolYear = SchoolYear::firstOrCreate(['year_start' => 2025, 'year_end' => 2026]);

        $semester = Semester::firstOrCreate(
            ['school_year_id' => $schoolYear->id, 'semester_name' => '2nd Semester'],
            ['is_active' => true]
        );

        if (!$semester->is_active) {
            $semester->update(['is_active' => true]);
        }

        // Teacher Subject
        $ts = TeacherSubject::firstOrCreate([
            'teacher_id'  => $teacher->id,
            'subject_id'  => $subject->id,
            'semester_id' => $semester->id,
            'section'     => 'BSIT 1-A',
        ]);

        // Exam
        $exam = Exam::firstOrCreate([
            'teacher_subject_id' => $ts->id,
            'exam_type'          => 'prelim',
        ], [
            'uploaded_by' => $admin->id,
        ]);

        // Student + Result
        $student = Student::firstOrCreate(
            ['student_code' => '2024-0001'],
            ['teacher_subject_id' => $ts->id, 'student_name' => 'Maria Santos']
        );

        ExamResult::firstOrCreate(
            ['student_id' => $student->id, 'exam_id' => $exam->id],
            ['raw_score' => 35, 'percentage' => 70.00, 'remark' => 'fail']
        );
    }
}