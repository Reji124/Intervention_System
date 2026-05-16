<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Illuminate\Support\Facades\Cache;

/**
 * AnalyticsService
 * 
 * Provides institutional-level KPIs and aggregated analytics.
 * Handles computation of dashboard metrics, caching for performance.
 */
class AnalyticsService
{
    private const CACHE_KEYS = [
        'analytics.overall_pass_rate',
        'analytics.total_failed_students',
        'analytics.highest_performing_department',
        'analytics.lowest_performing_department',
        'analytics.highest_risk_subject',
        'analytics.highest_risk_teacher',
        'analytics.most_difficult_exam_type',
        'analytics.intervention_success_rate',
    ];

    // Risk thresholds
    private const RISK_HIGH = 70;      // < 70% = high risk (red)
    private const RISK_MODERATE = 85;  // 70-84% = moderate risk (yellow)
    // >= 85% = low risk (green)

    /**
     * Get total uploaded exam result rows.
     */
    public function getTotalExamResults(): int
    {
        return ExamResult::count();
    }

    /**
     * Get overall school pass rate.
     */
    public function getOverallPassRate(): int
    {
        $cacheKey = 'analytics.overall_pass_rate';
        
        return Cache::remember($cacheKey, 3600, function () {
            $totalResults = ExamResult::count();
            if ($totalResults === 0) return 0;

            $passCount = ExamResult::where('remark', 'pass')->count();
            return (int) round(($passCount / $totalResults) * 100);
        });
    }

    /**
     * Get total failed students (across all exams).
     */
    public function getTotalFailedStudents(): int
    {
        $cacheKey = 'analytics.total_failed_students';
        
        return Cache::remember($cacheKey, 3600, function () {
            return ExamResult::where('remark', 'fail')->count();
        });
    }

    /**
     * Get highest performing department (by pass rate).
     */
    public function getHighestPerformingDepartment(): ?array
    {
        $cacheKey = 'analytics.highest_performing_department';
        
        return Cache::remember($cacheKey, 3600, function () {
            $departments = Department::with('courses.subjects.teacherSubjects.exams.examResults')
                ->get();

            $deptMetrics = $departments->map(function ($dept) {
                $totalResults = 0;
                $passCount = 0;

                $dept->courses->each(function ($course) use (&$totalResults, &$passCount) {
                    $course->subjects->each(function ($subject) use (&$totalResults, &$passCount) {
                        $subject->teacherSubjects->each(function ($ts) use (&$totalResults, &$passCount) {
                            $ts->exams->each(function ($exam) use (&$totalResults, &$passCount) {
                                $totalResults += $exam->examResults->count();
                                $passCount += $exam->examResults->where('remark', 'pass')->count();
                            });
                        });
                    });
                });

                $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

                return [
                    'id' => $dept->id,
                    'name' => $dept->department_name,
                    'pass_rate' => $passRate,
                    'total_results' => $totalResults,
                ];
            })->filter(fn ($dept) => $dept['total_results'] > 0)
                ->sortByDesc('pass_rate')
                ->first();

            return $deptMetrics;
        });
    }

    /**
     * Get lowest performing department (by pass rate).
     */
    public function getLowestPerformingDepartment(): ?array
    {
        $cacheKey = 'analytics.lowest_performing_department';
        
        return Cache::remember($cacheKey, 3600, function () {
            $departments = Department::with('courses.subjects.teacherSubjects.exams.examResults')
                ->get();

            $deptMetrics = $departments->map(function ($dept) {
                $totalResults = 0;
                $passCount = 0;

                $dept->courses->each(function ($course) use (&$totalResults, &$passCount) {
                    $course->subjects->each(function ($subject) use (&$totalResults, &$passCount) {
                        $subject->teacherSubjects->each(function ($ts) use (&$totalResults, &$passCount) {
                            $ts->exams->each(function ($exam) use (&$totalResults, &$passCount) {
                                $totalResults += $exam->examResults->count();
                                $passCount += $exam->examResults->where('remark', 'pass')->count();
                            });
                        });
                    });
                });

                $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

                return [
                    'id' => $dept->id,
                    'name' => $dept->department_name,
                    'pass_rate' => $passRate,
                    'total_results' => $totalResults,
                ];
            })->filter(fn ($dept) => $dept['total_results'] > 0)
                ->sortBy('pass_rate')
                ->first();

            return $deptMetrics;
        });
    }

    /**
     * Get highest risk subject (lowest pass rate).
     */
    public function getHighestRiskSubject(): ?array
    {
        $cacheKey = 'analytics.highest_risk_subject';
        
        return Cache::remember($cacheKey, 3600, function () {
            $subjects = Subject::with('teacherSubjects.exams.examResults')->get();

            $subjectMetrics = $subjects->map(function ($subject) {
                $totalResults = 0;
                $passCount = 0;

                $subject->teacherSubjects->each(function ($ts) use (&$totalResults, &$passCount) {
                    $ts->exams->each(function ($exam) use (&$totalResults, &$passCount) {
                        $totalResults += $exam->examResults->count();
                        $passCount += $exam->examResults->where('remark', 'pass')->count();
                    });
                });

                $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

                return [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'code' => $subject->subject_code,
                    'pass_rate' => $passRate,
                    'total_results' => $totalResults,
                ];
            })->filter(fn ($subject) => $subject['total_results'] > 0)
                ->sortBy('pass_rate')
                ->first();

            return $subjectMetrics;
        });
    }

    /**
     * Get highest risk teacher (lowest pass rate overall).
     */
    public function getHighestRiskTeacher(): ?array
    {
        $cacheKey = 'analytics.highest_risk_teacher';
        
        return Cache::remember($cacheKey, 3600, function () {
            $teachers = Teacher::with('teacherSubjects.exams.examResults')->get();

            $teacherMetrics = $teachers->map(function ($teacher) {
                $totalResults = 0;
                $passCount = 0;

                $teacher->teacherSubjects->each(function ($ts) use (&$totalResults, &$passCount) {
                    $ts->exams->each(function ($exam) use (&$totalResults, &$passCount) {
                        $totalResults += $exam->examResults->count();
                        $passCount += $exam->examResults->where('remark', 'pass')->count();
                    });
                });

                $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

                return [
                    'id' => $teacher->id,
                    'name' => $teacher->teacher_name,
                    'code' => $teacher->teacher_code,
                    'pass_rate' => $passRate,
                    'total_results' => $totalResults,
                ];
            })->filter(fn ($teacher) => $teacher['total_results'] > 0)
                ->sortBy('pass_rate')
                ->first();

            return $teacherMetrics;
        });
    }

    /**
     * Get most difficult exam type (lowest average pass rate).
     */
    public function getMostDifficultExamType(): ?array
    {
        $cacheKey = 'analytics.most_difficult_exam_type';
        
        return Cache::remember($cacheKey, 3600, function () {
            $examTypes = Exam::distinct('exam_type')->pluck('exam_type');

            $metrics = $examTypes->map(function ($examType) {
                $exams = Exam::where('exam_type', $examType)
                    ->with('examResults')
                    ->get();

                $totalResults = 0;
                $passCount = 0;

                $exams->each(function ($exam) use (&$totalResults, &$passCount) {
                    $totalResults += $exam->examResults->count();
                    $passCount += $exam->examResults->where('remark', 'pass')->count();
                });

                $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

                return [
                    'exam_type' => $examType,
                    'pass_rate' => $passRate,
                    'total_results' => $totalResults,
                ];
            })->filter(fn ($examType) => $examType['total_results'] > 0)
                ->sortBy('pass_rate')
                ->first();

            return $metrics;
        });
    }

    /**
     * Get intervention success rate (estimated as improvement from prelim to final).
     */
    public function getInterventionSuccessRate(): int
    {
        $cacheKey = 'analytics.intervention_success_rate';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Simplified: calculate as overall pass rate of final exams vs prelim
            $prelimExams = Exam::where('exam_type', 'prelim')->with('examResults')->get();
            $finalExams = Exam::where('exam_type', 'final')->with('examResults')->get();

            $prelimTotal = 0;
            $prelimPass = 0;
            $finalTotal = 0;
            $finalPass = 0;

            $prelimExams->each(function ($exam) use (&$prelimTotal, &$prelimPass) {
                $prelimTotal += $exam->examResults->count();
                $prelimPass += $exam->examResults->where('remark', 'pass')->count();
            });

            $finalExams->each(function ($exam) use (&$finalTotal, &$finalPass) {
                $finalTotal += $exam->examResults->count();
                $finalPass += $exam->examResults->where('remark', 'pass')->count();
            });

            $prelimRate = $prelimTotal > 0 ? ($prelimPass / $prelimTotal) * 100 : 0;
            $finalRate = $finalTotal > 0 ? ($finalPass / $finalTotal) * 100 : 0;

            $improvement = $finalRate - $prelimRate;
            return (int) round(max(0, $improvement));
        });
    }

    /**
     * Clear all analytics caches.
     */
    public function clearCache(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get risk level label and color for a given pass rate.
     */
    public static function getRiskLevel(?int $passRate, int $totalResults = 1): array
    {
        if ($totalResults <= 0 || $passRate === null) {
            return [
                'level' => 'none',
                'label' => 'No data',
                'color' => 'gray',
                'badge_class' => 'badge-muted',
            ];
        }

        if ($passRate >= self::RISK_MODERATE) {
            return [
                'level' => 'low',
                'label' => 'Low Risk',
                'color' => 'green',
                'badge_class' => 'badge-pass',
            ];
        } elseif ($passRate >= self::RISK_HIGH) {
            return [
                'level' => 'moderate',
                'label' => 'Moderate Risk',
                'color' => 'yellow',
                'badge_class' => 'badge-mid',
            ];
        } else {
            return [
                'level' => 'high',
                'label' => 'High Risk',
                'color' => 'red',
                'badge_class' => 'badge-fail',
            ];
        }
    }
}
