<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectCategory;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@school.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        // Student Assistant account
        User::create([
            'name'     => 'Maria Assistant',
            'email'    => 'assistant@school.com',
            'password' => bcrypt('password'),
            'role'     => 'assistant',
        ]);

        // Teacher (no login — managed by assistant)
        $teacher = Teacher::create([
            'teacher_name' => 'Juan dela Cruz',
        ]);

        $dept     = Department::create(['department_name' => 'BSIT']);
        $category = SubjectCategory::create(['category_name' => 'Major']);

        $subject = Subject::create([
            'department_id' => $dept->id,
            'category_id'   => $category->id,
            'subject_code'  => 'IT101',
            'subject_name'  => 'Programming 1',
            'year_level'    => '1st year',
        ]);

        $schoolYear = SchoolYear::create([
            'year_start' => 2025,
            'year_end'   => 2026,
        ]);

        $semester = Semester::create([
            'school_year_id' => $schoolYear->id,
            'semester_name'  => '2nd',
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