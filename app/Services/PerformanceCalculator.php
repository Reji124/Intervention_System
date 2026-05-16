<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * PerformanceCalculator
 * 
 * Calculates detailed performance metrics for teachers and subjects.
 * Handles pass rates, failure counts, mean scores, and exam-type breakdowns.
 */
class PerformanceCalculator
{
    private const EXAM_TYPES = [
        'prelim' => 'Prelim',
        'midterm' => 'Midterm',
        'prefinal' => 'Prefinal',
        'final' => 'Final',
    ];

    /**
     * Get performance summary for a teacher across all exam types.
     * 
     * Returns array with exam types as keys and metrics as values:
     * [
     *   'Prelim' => ['pass_rate' => 85, 'failed_students' => 5, 'mean_score' => 78.5, 'difficulty' => 'Moderate'],
     *   'Midterm' => [...],
     *   ...
     * ]
     */
    public function getTeacherPerformanceSummary(Teacher $teacher): array
    {
        $summary = [];

        // Load all data once instead of per exam type
        $teacherSubjects = $teacher->teacherSubjects()
            ->with(['exams.examResults'])
            ->get();

        foreach (self::EXAM_TYPES as $examType => $examLabel) {
            $totalResults = 0;
            $passCount    = 0;
            $failCount    = 0;
            $totalScore   = 0;

            foreach ($teacherSubjects as $ts) {
                // Filter exams by type HERE, after eager load
                $examsOfType = $ts->exams->where('exam_type', $examType);

                foreach ($examsOfType as $exam) {
                    foreach ($exam->examResults as $result) {
                        $totalResults++;
                        $totalScore += $result->percentage;

                        if ($result->remark === 'pass') {
                            $passCount++;
                        } else {
                            $failCount++;
                        }
                    }
                }
            }

            $passRate  = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;
            $meanScore = $totalResults > 0 ? round($totalScore / $totalResults, 2) : 0;

            $summary[$examLabel] = [
                'pass_rate'       => $passRate,
                'failed_students' => $failCount,
                'mean_score'      => $meanScore,
                'difficulty'      => $totalResults > 0 ? $this->estimateDifficulty($passRate) : 'No data',
                'total_students'  => $totalResults,
            ];
        }

        return $summary;
    }

    /**
     * Get overall performance totals for a teacher.
     */
    public function getTeacherOverallMetrics(Teacher $teacher): array
    {
        $exams = $teacher->teacherSubjects()
            ->with('exams.examResults')
            ->get();

        $totalResults = 0;
        $passCount = 0;
        $failCount = 0;
        $totalScore = 0;

        $exams->each(function ($ts) use (&$totalResults, &$passCount, &$failCount, &$totalScore) {
            $ts->exams->each(function ($exam) use (&$totalResults, &$passCount, &$failCount, &$totalScore) {
                $exam->examResults->each(function ($result) use (&$totalResults, &$passCount, &$failCount, &$totalScore) {
                    $totalResults++;
                    if ($result->remark === 'pass') {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
                    $totalScore += $result->percentage;
                });
            });
        });

        $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;
        $failureRate = 100 - $passRate;
        $meanScore = $totalResults > 0 ? round($totalScore / $totalResults, 2) : 0;

        return [
            'pass_rate' => $passRate,
            'failure_rate' => $failureRate,
            'failed_students' => $failCount,
            'mean_score' => $meanScore,
            'total_students' => $totalResults,
        ];
    }

    /**
     * Get subject performance breakdown for a teacher.
     * 
     * Returns array of subjects with performance metrics.
     */
    public function getTeacherSubjectBreakdown(Teacher $teacher): array
    {
        $teacherSubjects = $teacher->teacherSubjects()
            ->with(['subject', 'exams.examResults', 'students'])
            ->get();

        return $teacherSubjects->map(function ($ts) {
            $totalResults = 0;
            $passCount = 0;
            $failCount = 0;
            $totalScore = 0;
            $interventionCount = 0;

            $ts->exams->each(function ($exam) use (&$totalResults, &$passCount, &$failCount, &$totalScore, &$interventionCount) {
                $exam->examResults->each(function ($result) use (&$totalResults, &$passCount, &$failCount, &$totalScore, &$interventionCount) {
                    $totalResults++;
                    if ($result->remark === 'pass') {
                        $passCount++;
                    } else {
                        $failCount++;
                        $interventionCount++;
                    }
                    $totalScore += $result->percentage;
                });
            });

            $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;
            $riskLevel = AnalyticsService::getRiskLevel($passRate);

            return [
                'subject_id' => $ts->subject->id,
                'subject_name' => $ts->subject->subject_name,
                'subject_code' => $ts->subject->subject_code,
                'total_students' => $ts->students->count(),
                'pass_rate' => $passRate,
                'failed_students' => $failCount,
                'intervention_count' => $interventionCount,
                'mean_score' => $totalResults > 0 ? round($totalScore / $totalResults, 2) : 0,
                'risk_level' => $riskLevel['level'],
                'risk_label' => $riskLevel['label'],
            ];
        })->toArray();
    }

    /**
     * Compare teachers teaching the same subject.
     */
    public function compareTeachersForSubject(Subject $subject): array
    {
        $teacherSubjects = $subject->teacherSubjects()
            ->with(['teacher', 'exams.examResults'])
            ->get();

        return $teacherSubjects->map(function ($ts) {
            $totalResults = 0;
            $passCount = 0;

            $ts->exams->each(function ($exam) use (&$totalResults, &$passCount) {
                $totalResults += $exam->examResults->count();
                $passCount += $exam->examResults->where('remark', 'pass')->count();
            });

            $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

            return [
                'teacher_id' => $ts->teacher->id,
                'teacher_name' => $ts->teacher->teacher_name,
                'pass_rate' => $passRate,
                'total_students' => $totalResults,
            ];
        })->sortByDesc('pass_rate')->toArray();
    }

    /**
     * Get department-wide performance metrics.
     */
    public function getDepartmentMetrics($department): array
    {
        $courses = $department->courses()->with('subjects.teacherSubjects.exams.examResults')->get();

        $totalResults = 0;
        $passCount = 0;
        $teacherIds = [];
        $subjectMetrics = [];

        $courses->each(function ($course) use (&$totalResults, &$passCount, &$teacherIds, &$subjectMetrics) {
            $course->subjects->each(function ($subject) use (&$totalResults, &$passCount, &$teacherIds, &$subjectMetrics) {
                $subject->teacherSubjects->each(function ($ts) use (&$totalResults, &$passCount, &$teacherIds, &$subjectMetrics, $subject) {
                    $teacherIds[$ts->teacher_id] = true;
                    
                    $subjectTotal = 0;
                    $subjectPass = 0;

                    $ts->exams->each(function ($exam) use (&$totalResults, &$passCount, &$subjectTotal, &$subjectPass) {
                        $exam->examResults->each(function ($result) use (&$totalResults, &$passCount, &$subjectTotal, &$subjectPass) {
                            $totalResults++;
                            $subjectTotal++;
                            if ($result->remark === 'pass') {
                                $passCount++;
                                $subjectPass++;
                            }
                        });
                    });

                    $subjectPassRate = $subjectTotal > 0 ? (int) round(($subjectPass / $subjectTotal) * 100) : 0;
                    $riskLevel = AnalyticsService::getRiskLevel($subjectPassRate);

                    $subjectMetrics[$subject->id] = [
                        'subject_name' => $subject->subject_name,
                        'pass_rate' => $subjectPassRate,
                        'risk_level' => $riskLevel['level'],
                    ];
                });
            });
        });

        $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

        return [
            'pass_rate' => $passRate,
            'total_teachers' => count($teacherIds),
            'total_students' => $totalResults,
            'highest_risk_subject' => collect($subjectMetrics)->sortBy('pass_rate')->first(),
            'top_performing_teacher' => null, // Will be filled by caller
        ];
    }

    /**
     * Estimate difficulty level based on pass rate.
     */
    private function estimateDifficulty(int $passRate): string
    {
        if ($passRate >= 85) {
            return 'Easy';
        } elseif ($passRate >= 70) {
            return 'Moderate';
        } else {
            return 'Difficult';
        }
    }

    /**
     * Get trend data for a teacher (pass rate progression: Prelim → Midterm → Prefinal → Final).
     */
    public function getTeacherTrendData(Teacher $teacher): array
    {
        $summary = $this->getTeacherPerformanceSummary($teacher);
        $trendData = [];

        foreach (self::EXAM_TYPES as $examLabel) {
            $trendData[] = [
                'exam_type' => $examLabel,
                'pass_rate' => $summary[$examLabel]['pass_rate'] ?? 0,
            ];
        }

        return $trendData;
    }
}
