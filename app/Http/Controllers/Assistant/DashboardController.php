<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\TeacherSubject;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats chips ───────────────────────────────────────────────────────

        $totalTeachers = Teacher::count();

        $totalExamsUploaded = Exam::count();

        // Pending = every teacher-subject combo × 3 exam types, minus what's uploaded.
        // We treat each (teacher_subject_id, exam_type) pair as one expected exam slot.
        $totalExpectedExams = TeacherSubject::count() * 3; // prelim, midterm, final
        $examsPendingUpload = max(0, $totalExpectedExams - $totalExamsUploaded);

        // ── Recent exams uploaded — grouped by semester ───────────────────────
        //
        // The Exam model itself has no semester column; semester lives on
        // TeacherSubject → Semester (via teacher_subject_id → semester_id).
        // We eager-load that chain and then group in PHP so we avoid a raw join.

        $recentExams = Exam::with([
                'teacherSubject.subject',
                'teacherSubject.teacher',
                'teacherSubject.semester',   // adjust relation name if different
                'uploadedBy',
            ])
            ->latest()
            ->take(60)                       // pull enough rows to fill all 3 tabs
            ->get()
            ->filter(fn($e) =>
                $e->teacherSubject
                && $e->teacherSubject->subject
                && $e->teacherSubject->teacher
            )
            ->groupBy(function ($exam) {
                // Normalise semester name to one of: '1st' | '2nd' | 'summer'
                $semName = strtolower(
                    optional($exam->teacherSubject->semester)->semester_name ?? ''
                );

                if (str_contains($semName, '1st') || str_contains($semName, 'first'))  return '1st';
                if (str_contains($semName, '2nd') || str_contains($semName, 'second')) return '2nd';
                if (str_contains($semName, 'summer'))                                  return 'summer';

                return 'other'; // safety bucket — won't show in tabs
            })
            ->map(fn($group) => $group->take(15)); // cap each tab to 15 rows

        // ── Teachers overview — exam upload count ─────────────────────────────

        $teachers = Teacher::withCount('teacherSubjects')
            ->get()
            ->map(function ($teacher) {
                $teacher->exams_uploaded_count = Exam::whereHas(
                    'teacherSubject',
                    fn($q) => $q->where('teacher_id', $teacher->id)
                )->count();

                return $teacher;
            })
            ->sortBy('teacher_name')
            ->values();

        // ── Active semester (kept for layout / breadcrumb use) ────────────────

        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.dashboard', compact(
            'totalTeachers',
            'totalExamsUploaded',
            'examsPendingUpload',
            'recentExams',
            'teachers',
            'activeSemester',
        ));
    }
}