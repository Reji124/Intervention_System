<?php

// app/Http/Controllers/Assistant/InterventionController.php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Semester;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function index()
    {
        // Load ALL teacher-subjects that have at least one exam result,
        // regardless of pass/fail — so ALL subjects show up per teacher.
        $teacherSubjects = TeacherSubject::with([
                'teacher',
                'subject',
                'semester.schoolYear',
                'exams.examResults.student',
                'exams.examResults.exam',
            ])
            ->orderBy('teacher_id')
            ->get();

        // Build: teacher_name → [ label → subjectData ]
        $grouped = $teacherSubjects
            ->groupBy(fn($ts) => $ts->teacher->teacher_name)
            ->map(fn($teacherTSList) =>
                $teacherTSList->mapWithKeys(function ($ts) {
                    $allResults     = $ts->exams->flatMap(fn($e) => $e->examResults);
                    $failingResults = $allResults->where('remark', 'fail')->sortBy('percentage')->values();

                    // Prefer exam with matrix data, fall back to any exam
                    $examWithMatrix = $ts->exams->first(fn($e) => !empty($e->item_matrix_data));
                    $anyExam        = $ts->exams->first();

                    $label = $ts->subject->subject_code . ' — ' . $ts->section;

                    return [$label => [
                        'teacher_subject' => $ts,
                        'label'           => $label,
                        'all_results'     => $allResults,
                        'failing_results' => $failingResults,
                        'pass_count'      => $allResults->where('remark', 'pass')->count(),
                        'fail_count'      => $failingResults->count(),
                        'total_count'     => $allResults->count(),
                        'exam'            => $examWithMatrix ?? $anyExam,
                    ]];
                })
            );

        $totalFailing   = ExamResult::where('remark', 'fail')->count();
        $totalPassing   = ExamResult::where('remark', 'pass')->count();
        $activeSemester = Semester::with('schoolYear')->latest()->first();

        return view('assistant.interventions.index', compact(
            'grouped',
            'totalFailing',
            'totalPassing',
            'activeSemester',
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
}