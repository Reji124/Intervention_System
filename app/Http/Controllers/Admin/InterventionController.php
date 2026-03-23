<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $schoolYears    = collect();
        $semesters      = collect();
        $departments    = collect();
        $subjects       = collect();
        $teachers       = collect();
        $categories     = collect();
        $exams          = collect();
        $activeSemester = null;
        $totalFailing   = 0;
        $totalPassing   = 0;

        $selectedSY      = $request->input('school_year_id');
        $selectedSem     = $request->input('semester_id');
        $selectedDept    = $request->input('department_id');
        $selectedCat     = $request->input('category');
        $selectedSubject = $request->input('subject_id');
        $selectedTeacher = $request->input('teacher_id');

        // filled() returns false for empty strings, has() does not.
        $isFilteredRequest = $request->filled('_filtered');

        try {
            $schoolYears = SchoolYear::with('semesters')->orderByDesc('year_start')->get();
            $semesters   = Semester::with('schoolYear')->orderByDesc('id')->get();
            $departments = Department::orderBy('department_name')->get();
            $subjects    = Subject::with('department')->orderBy('subject_code')->get();
            $teachers    = Teacher::with(['teacherSubjects.subject', 'teacherSubjects.semester'])
                                  ->orderBy('teacher_name')
                                  ->get();

            $categories = DB::table('subjects')
                            ->select(DB::raw('DISTINCT category'))
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->orderBy('category')
                            ->pluck('category');

            // Prefer is_active flag; fall back to latest if none is flagged.
            $activeSemester = Semester::with('schoolYear')
                                ->where('is_active', true)
                                ->first()
                              ?? Semester::with('schoolYear')->latest()->first();

            // Only apply the default semester on a true first load —
            // not when the user has explicitly submitted the filter form.
            if (!$isFilteredRequest && !$selectedSem && !$selectedSY) {
                $selectedSem = $activeSemester?->id;
            }

            $exams = Exam::with([
                    'teacherSubject.teacher',
                    'teacherSubject.subject.department',
                    'teacherSubject.semester.schoolYear',
                    // Eager-load examResults WITH student so the model's
                    // computed attributes (total_students, pass_count, etc.)
                    // work off the already-loaded collection and don't fire
                    // extra queries per exam.
                    'examResults.student',
                ])
                ->whereHas('teacherSubject', function ($q) use (
                    $selectedSY, $selectedSem, $selectedDept,
                    $selectedCat, $selectedSubject, $selectedTeacher
                ) {
                    if ($selectedSem) {
                        $q->where('teacher_subjects.semester_id', $selectedSem);
                    } elseif ($selectedSY) {
                        $q->whereHas('semester', fn($s) =>
                            $s->where('school_year_id', $selectedSY));
                    }

                    if ($selectedDept) {
                        $q->whereHas('subject', fn($s) =>
                            $s->where('department_id', $selectedDept));
                    }

                    // PostgreSQL string comparison is case-sensitive;
                    // LOWER() on both sides makes category matching safe.
                    if ($selectedCat) {
                        $q->whereHas('subject', fn($s) =>
                            $s->whereRaw('LOWER(category) = LOWER(?)', [$selectedCat]));
                    }

                    if ($selectedSubject) {
                        $q->where('teacher_subjects.subject_id', $selectedSubject);
                    }

                    if ($selectedTeacher) {
                        $q->where('teacher_subjects.teacher_id', $selectedTeacher);
                    }
                })
                ->orderByDesc('created_at')
                ->get()
                // Drop any exams with broken relationships so the view
                // never gets a null teacherSubject / subject / teacher / semester.
                ->filter(fn($exam) =>
                    $exam->teacherSubject &&
                    $exam->teacherSubject->subject &&
                    $exam->teacherSubject->teacher &&
                    $exam->teacherSubject->semester
                )
                ->values();

            // Scope the header pills to only what's visible on screen.
            $examIds      = $exams->pluck('id');
            $totalFailing = ExamResult::whereIn('exam_id', $examIds)->where('remark', 'fail')->count();
            $totalPassing = ExamResult::whereIn('exam_id', $examIds)->where('remark', 'pass')->count();

        } catch (\Exception $e) {
            Log::error('InterventionController@index: ' . $e->getMessage(), [
                'filters' => $request->only([
                    'school_year_id', 'semester_id', 'department_id',
                    'category', 'subject_id', 'teacher_id',
                ]),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return view('admin.interventions.index', compact(
            'schoolYears', 'semesters', 'departments', 'categories',
            'subjects', 'teachers', 'exams',
            'totalFailing', 'totalPassing', 'activeSemester',
            'selectedSY', 'selectedSem', 'selectedDept',
            'selectedCat', 'selectedSubject', 'selectedTeacher',
        ));
    }

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

    public function destroyResult(ExamResult $examResult)
    {
        $examResult->delete();
        return response()->json(['success' => true]);
    }

    public function destroyExam(Exam $exam)
    {
        $exam->examResults()->delete();
        $exam->delete();
        return response()->json(['success' => true]);
    }
}