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
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $schoolYears = SchoolYear::with('semesters')->orderByDesc('year_start')->get();
            $semesters   = Semester::with('schoolYear')->orderByDesc('id')->get();
            $departments = Department::orderBy('department_name')->get();
            $subjects    = Subject::with('department')->orderBy('subject_code')->get();

            // Teachers need teacherSubjects for the cascading filter data-attributes
            $teachers = Teacher::with(['teacherSubjects.subject', 'teacherSubjects.semester'])
                                ->orderBy('teacher_name')
                                ->get();

            // Categories are plain strings from subjects.category
            $categories = DB::table('subjects')
                            ->select('category')
                            ->distinct()
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->orderBy('category')
                            ->pluck('category');

            // Active semester = the one marked active, fallback to latest
            $activeSemester = Semester::with('schoolYear')
                                ->where('is_active', true)
                                ->latest()
                                ->first()
                ?? Semester::with('schoolYear')->latest()->first();

            $selectedSY      = $request->input('school_year_id');
            $selectedSem     = $request->input('semester_id',
                                    $request->has('_filtered') ? null : $activeSemester?->id);
            $selectedDept    = $request->input('department_id');
            $selectedCat     = $request->input('category');
            $selectedSubject = $request->input('subject_id');
            $selectedTeacher = $request->input('teacher_id');

            $examQuery = Exam::with([
                    'teacherSubject.teacher',
                    'teacherSubject.subject.department',
                    'teacherSubject.semester.schoolYear',
                    'examResults.student',
                ])
                ->whereHas('teacherSubject', function ($q) use (
                    $selectedSY, $selectedSem, $selectedDept,
                    $selectedCat, $selectedSubject, $selectedTeacher
                ) {
                    // Semester filter takes priority over SY-only filter
                    if ($selectedSem) {
                        $q->where('teacher_subjects.semester_id', $selectedSem);
                    } elseif ($selectedSY) {
                        $q->whereHas('semester', fn($s) =>
                            $s->where('semesters.school_year_id', $selectedSY));
                    }

                    if ($selectedDept) {
                        $q->whereHas('subject', fn($s) =>
                            $s->where('subjects.department_id', $selectedDept));
                    }

                    if ($selectedCat) {
                        $q->whereHas('subject', fn($s) =>
                            $s->where('subjects.category', $selectedCat));
                    }

                    if ($selectedSubject) {
                        $q->where('teacher_subjects.subject_id', $selectedSubject);
                    }

                    if ($selectedTeacher) {
                        $q->where('teacher_subjects.teacher_id', $selectedTeacher);
                    }
                })
                ->orderByDesc('created_at');

            $exams = $examQuery->get()->map(function ($exam) {
                // Guard against missing relationship
                if (!$exam->teacherSubject || !$exam->teacherSubject->subject) {
                    return null;
                }

                $results              = $exam->examResults ?? collect();
                $exam->total_students = $results->count();
                $exam->pass_count     = $results->where('remark', 'pass')->count();
                $exam->fail_count     = $results->where('remark', 'fail')->count();
                $exam->pass_rate      = $exam->total_students > 0
                    ? round(($exam->pass_count / $exam->total_students) * 100) : 0;

                return $exam;
            })->filter()->values(); // remove any nulls from broken relationships

            // These reflect ALL records, not just filtered ones
            $totalFailing = ExamResult::where('remark', 'fail')->count();
            $totalPassing = ExamResult::where('remark', 'pass')->count();

        } catch (\Exception $e) {
            // Never show a 500 — return empty state with error notice
            $exams        = collect();
            $totalFailing = 0;
            $totalPassing = 0;

            // Keep filter dropdowns working even on error
            $schoolYears    = $schoolYears    ?? collect();
            $semesters      = $semesters      ?? collect();
            $departments    = $departments    ?? collect();
            $subjects       = $subjects       ?? collect();
            $teachers       = $teachers       ?? collect();
            $categories     = $categories     ?? collect();
            $activeSemester = $activeSemester ?? null;

            $selectedSY      = $request->input('school_year_id');
            $selectedSem     = $request->input('semester_id');
            $selectedDept    = $request->input('department_id');
            $selectedCat     = $request->input('category');
            $selectedSubject = $request->input('subject_id');
            $selectedTeacher = $request->input('teacher_id');

            \Log::error('InterventionController@index failed: ' . $e->getMessage(), [
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
        $exam->examResults()->delete();
        $exam->delete();
        return response()->json(['success' => true]);
    }
}