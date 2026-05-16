<?php

// app/Http/Controllers/Assistant/InterventionController.php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Semester;
use App\Models\TeacherSubject;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class InterventionController extends Controller
{

    public function index()
    {
        $teacherSubjects = TeacherSubject::with([
                'teacher',
                'subject',
                'semester.schoolYear',
                'exams.examResults.student',
                'exams.examResults.exam',
            ])
            ->orderBy('teacher_id')
            ->get();

        $grouped = $teacherSubjects
            ->filter(fn($ts) => $ts->teacher && $ts->subject)
            ->groupBy(fn($ts) => $ts->teacher->teacher_name)
            ->map(fn($teacherTSList) =>
                $teacherTSList->groupBy(function ($ts) {
                    return $ts->subject->subject_code
                        . ' — ' . $ts->subject->subject_name
                        . ($ts->section ? ' (' . $ts->section . ')' : '');
                })->map(function ($subjectTSList) {

                    $allSubjectResults = $subjectTSList->flatMap(
                        fn($ts) => $ts->exams->flatMap(fn($e) => $e->examResults)
                    );

                    // Group by exam_type within this subject
                    $examTypes = $subjectTSList->flatMap(fn($ts) => $ts->exams)
                        ->groupBy('exam_type')
                        ->map(function ($examsOfType) {
                            $allResults     = $examsOfType->flatMap(fn($e) => $e->examResults);
                            $failingResults = $allResults->where('remark', 'fail')
                                                        ->sortBy('percentage')
                                                        ->values();
                            $examWithMatrix = $examsOfType->first(fn($e) => !empty($e->item_matrix_data));
                            $anyExam        = $examsOfType->first();

                            return [
                                'all_results'     => $allResults,
                                'failing_results' => $failingResults,
                                'pass_count'      => $allResults->where('remark', 'pass')->count(),
                                'fail_count'      => $failingResults->count(),
                                'total_count'     => $allResults->count(),
                                'exam'            => $examWithMatrix ?? $anyExam,
                            ];
                        });

                    return [
                        'exam_types'  => $examTypes,
                        'pass_count'  => $allSubjectResults->where('remark', 'pass')->count(),
                        'fail_count'  => $allSubjectResults->where('remark', 'fail')->count(),
                        'total_count' => $allSubjectResults->count(),
                        'pass_rate'   => $allSubjectResults->count() > 0
                            ? round(($allSubjectResults->where('remark', 'pass')->count() / $allSubjectResults->count()) * 100)
                            : 0,
                    ];
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

        $this->clearAnalyticsCache();

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
        $this->clearAnalyticsCache();

        return response()->json(['success' => true]);
    }

    private function clearAnalyticsCache(): void
    {
        app(AnalyticsService::class)->clearCache();
    }
}
