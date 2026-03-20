<?php

// app/Http/Controllers/Admin/InterventionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectCategory;
use App\Models\Teacher;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $schoolYears = SchoolYear::with('semesters')->orderByDesc('year_start')->get();
        $semesters   = Semester::with('schoolYear')->orderByDesc('id')->get();
        $departments = Department::withCount('subjects')->orderBy('department_name')->get();
        $categories  = SubjectCategory::orderBy('category_name')->get();
        $subjects    = Subject::with(['department','category'])->orderBy('subject_code')->get();
        $teachers    = Teacher::orderBy('teacher_name')->get();

        $latestSemester = Semester::with('schoolYear')->latest()->first();

        $selectedSY      = $request->input('school_year_id');
        $selectedSem     = $request->input('semester_id',
                                $request->has('_filtered') ? null : $latestSemester?->id);
        $selectedDept    = $request->input('department_id');
        $selectedCat     = $request->input('category_id');
        $selectedSubject = $request->input('subject_id');
        $selectedTeacher = $request->input('teacher_id');

        $examQuery = Exam::with([
                'teacherSubject.teacher',
                'teacherSubject.subject.department',
                'teacherSubject.subject.category',
                'teacherSubject.semester.schoolYear',
                'examResults.student',
            ])
            ->whereHas('teacherSubject', function ($q) use (
                $selectedSY, $selectedSem, $selectedDept,
                $selectedCat, $selectedSubject, $selectedTeacher
            ) {
                if ($selectedSem) {
                    $q->where('semester_id', $selectedSem);
                } elseif ($selectedSY) {
                    $q->whereHas('semester', fn($s) =>
                        $s->where('school_year_id', $selectedSY));
                }
                if ($selectedDept)    $q->whereHas('subject', fn($s) => $s->where('department_id', $selectedDept));
                if ($selectedCat)     $q->whereHas('subject', fn($s) => $s->where('category_id', $selectedCat));
                if ($selectedSubject) $q->where('subject_id', $selectedSubject);
                if ($selectedTeacher) $q->where('teacher_id', $selectedTeacher);
            })
            ->orderByDesc('created_at');

        $exams = $examQuery->get()->map(function ($exam) {
            $results              = $exam->examResults;
            $exam->total_students = $results->count();
            $exam->pass_count     = $results->where('remark', 'pass')->count();
            $exam->fail_count     = $results->where('remark', 'fail')->count();
            $exam->pass_rate      = $exam->total_students > 0
                ? round(($exam->pass_count / $exam->total_students) * 100) : 0;
            return $exam;
        });

        $totalFailing   = ExamResult::where('remark', 'fail')->count();
        $totalPassing   = ExamResult::where('remark', 'pass')->count();
        $activeSemester = $latestSemester;

        return view('admin.interventions.index', compact(
            'schoolYears', 'semesters', 'departments', 'categories',
            'subjects', 'teachers', 'exams',
            'totalFailing', 'totalPassing', 'activeSemester',
            'selectedSY', 'selectedSem', 'selectedDept',
            'selectedCat', 'selectedSubject', 'selectedTeacher',
        ));
    }

    // ── Update a single exam result ───────────────────────────────────────────
    public function updateResult(Request $request, ExamResult $examResult)
    {
        $request->validate([
            'raw_score' => 'required|integer|min:0',
            'total'     => 'required|integer|min:1',
        ]);

        $rawScore   = (int) $request->raw_score;
        $total      = (int) $request->total;
        $percentage = round(($rawScore / $total) * 100, 2);
        $remark     = $percentage >= 75.0 ? 'pass' : 'fail';

        $examResult->update([
            'raw_score'  => $rawScore,
            'percentage' => $percentage,
            'remark'     => $remark,
        ]);

        return response()->json([
            'success'    => true,
            'raw_score'  => $rawScore,
            'percentage' => $percentage,
            'remark'     => $remark,
        ]);
    }

    // ── Delete a single exam result ───────────────────────────────────────────
    public function destroyResult(ExamResult $examResult)
    {
        $examResult->delete();

        return response()->json(['success' => true]);
    }

    // ── Delete an entire exam + all its results ───────────────────────────────
    public function destroyExam(Exam $exam)
    {
        // examResults cascade-deleted via model or we do it explicitly
        $exam->examResults()->delete();
        $exam->delete();

        return response()->json(['success' => true]);
    }
}