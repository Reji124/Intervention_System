<?php

// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\Semester;    

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats ──────────────────────────────────────
        $totalTeachers        = Teacher::count();
        $totalSubjects        = Subject::count();
        $totalStudents        = Student::count();
        $failingStudents = ExamResult::where('remark', 'fail')
                              ->distinct()
                              ->count('student_id');
        $newTeachersThisMonth = Teacher::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at',  now()->year)
                                       ->count();

        // ── Recent exam results (latest 8) ────────────
        $recentResults = ExamResult::with([
                'student',
                'exam.teacherSubject.subject',
            ])
            ->latest()
            ->take(8)
            ->get();

        // ── Active semester ────────────────────────────
        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('admin.dashboard', compact(
            'totalTeachers',
            'totalSubjects',
            'totalStudents',
            'failingStudents',
            'newTeachersThisMonth',
            'recentResults',
            'activeSemester',
        ));
    }
}