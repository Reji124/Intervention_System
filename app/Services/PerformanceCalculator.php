<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Semester;
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
     * Optionally filters by semester.
     * 
     * Returns array with exam types as keys and metrics as values:
     * [
     *   'Prelim' => ['pass_rate' => 85, 'failed_students' => 5, 'mean_score' => 78.5, 'remark' => 'Good'],
     *   'Midterm' => [...],
     *   ...
     * ]
     */
    public function getTeacherPerformanceSummary(Teacher $teacher, ?Semester $semester = null): array
    {
        $summary = [];
        $remarkCalc = new RemarkCalculator();

        // Load all data once instead of per exam type
        $query = $teacher->teacherSubjects()
            ->with(['exams.examResults']);
        
        if ($semester) {
            $query->where('semester_id', $semester->id);
        }
        
        $teacherSubjects = $query->get();

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
                'remark'          => $totalResults > 0 ? $remarkCalc->getRemarkLabel($passRate) : 'No data',
                'remark_class'    => $totalResults > 0 ? $remarkCalc->getBadgeClass($passRate) : 'none',
                'total_students'  => $totalResults,
            ];
        }

        return $summary;
    }

    /**
     * Get overall performance totals for a teacher.
     * Optionally filters by semester.
     */
    public function getTeacherOverallMetrics(Teacher $teacher, ?Semester $semester = null): array
    {
        $remarkCalc = new RemarkCalculator();

        $query = $teacher->teacherSubjects()
            ->with('exams.examResults');
        
        if ($semester) {
            $query->where('semester_id', $semester->id);
        }
        
        $exams = $query->get();

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
        $failureRate = $totalResults > 0 ? 100 - $passRate : 0;
        $meanScore = $totalResults > 0 ? round($totalScore / $totalResults, 2) : 0;

        return [
            'pass_rate' => $passRate,
            'failure_rate' => $failureRate,
            'failed_students' => $failCount,
            'mean_score' => $meanScore,
            'remark' => $totalResults > 0 ? $remarkCalc->getRemarkLabel($passRate) : 'No data',
            'remark_class' => $totalResults > 0 ? $remarkCalc->getBadgeClass($passRate) : 'none',
            'total_students' => $totalResults,
        ];
    }

    /**
     * Get subject performance breakdown for a teacher.
     * Optionally filters by semester.
     * 
     * Returns array of subjects with performance metrics.
     */
    public function getTeacherSubjectBreakdown(Teacher $teacher, ?Semester $semester = null): array
    {
        $remarkCalc = new RemarkCalculator();

        $query = $teacher->teacherSubjects()
            ->with(['subject', 'exams.examResults', 'students']);
        
        if ($semester) {
            $query->where('semester_id', $semester->id);
        }
        
        $teacherSubjects = $query->get();

        return $teacherSubjects->map(function ($ts) use ($remarkCalc) {
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
            $riskLevel = AnalyticsService::getRiskLevel($passRate, $totalResults);

            return [
                'subject_id' => $ts->subject->id,
                'subject_name' => $ts->subject->subject_name,
                'subject_code' => $ts->subject->subject_code,
                'total_students' => $ts->students->count(),
                'total_results' => $totalResults,
                'pass_rate' => $passRate,
                'failed_students' => $failCount,
                'intervention_count' => $interventionCount,
                'mean_score' => $totalResults > 0 ? round($totalScore / $totalResults, 2) : 0,
                'remark' => $totalResults > 0 ? $remarkCalc->getRemarkLabel($passRate) : 'No data',
                'remark_class' => $totalResults > 0 ? $remarkCalc->getBadgeClass($passRate) : 'none',
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
     * Optionally filters by semester.
     */
    public function getDepartmentMetrics($department, ?Semester $semester = null): array
    {
        $remarkCalc = new RemarkCalculator();

        $coursesQuery = $department->courses();
        
        if ($semester) {
            $coursesQuery->with(['subjects.teacherSubjects' => function ($q) {
                $q->where('semester_id', $semester->id);
            }, 'subjects.teacherSubjects.exams.examResults']);
        } else {
            $coursesQuery->with('subjects.teacherSubjects.exams.examResults');
        }
        
        $courses = $coursesQuery->get();

        $totalResults = 0;
        $passCount = 0;
        $teacherIds = [];
        $subjectMetrics = [];

        $courses->each(function ($course) use (&$totalResults, &$passCount, &$teacherIds, &$subjectMetrics, $remarkCalc) {
            $course->subjects->each(function ($subject) use (&$totalResults, &$passCount, &$teacherIds, &$subjectMetrics, $remarkCalc) {
                $subject->teacherSubjects->each(function ($ts) use (&$totalResults, &$passCount, &$teacherIds, &$subjectMetrics, $subject, $remarkCalc) {
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
                    $riskLevel = AnalyticsService::getRiskLevel($subjectPassRate, $subjectTotal);

                    $subjectMetrics[$subject->id] = [
                        'subject_name' => $subject->subject_name,
                        'pass_rate' => $subjectPassRate,
                        'total_results' => $subjectTotal,
                        'remark' => $subjectTotal > 0 ? $remarkCalc->getRemarkLabel($subjectPassRate) : 'No data',
                        'risk_level' => $riskLevel['level'],
                    ];
                });
            });
        });

        $passRate = $totalResults > 0 ? (int) round(($passCount / $totalResults) * 100) : 0;

        return [
            'pass_rate' => $passRate,
            'remark' => $totalResults > 0 ? $remarkCalc->getRemarkLabel($passRate) : 'No data',
            'total_teachers' => count($teacherIds),
            'total_students' => $totalResults,
            'highest_risk_subject' => collect($subjectMetrics)
                ->filter(fn ($subject) => ($subject['total_results'] ?? 0) > 0)
                ->sortBy('pass_rate')
                ->first(),
            'top_performing_teacher' => null, // Will be filled by caller
        ];
    }

    /**
     * Get trend data for a teacher (pass rate progression: Prelim → Midterm → Prefinal → Final).
     */
    public function getTeacherTrendData(Teacher $teacher, ?Semester $semester = null): array
    {
        $summary = $this->getTeacherPerformanceSummary($teacher, $semester);
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
