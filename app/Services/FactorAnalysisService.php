<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Exam;
use Illuminate\Support\Collection;

/**
 * FactorAnalysisService
 * 
 * Analyzes performance data to detect possible causes of low performance:
 * - Exam Factor: Assessment difficulty or construction issues
 * - Teacher Factor: Instructional delivery concerns
 * - Student Factor: Preparedness or engagement concerns
 * - Curriculum Factor: Alignment or content issues
 * 
 * Returns confidence percentages (0-100) for each factor.
 */
class FactorAnalysisService
{
    /**
     * Analyze performance of a teacher to determine possible factors.
     * 
     * Returns array with factors and confidence ratings.
     */
    public function analyzeTeacherPerformance(Teacher $teacher): array
    {
        $performanceCalc = new PerformanceCalculator();
        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher);

        $examFactorConfidence = $this->calculateExamFactorConfidence($teacher, $summary);
        $teacherFactorConfidence = $this->calculateTeacherFactorConfidence($teacher, $subjectBreakdown);
        $studentFactorConfidence = $this->calculateStudentFactorConfidence($teacher);
        $curriculumFactorConfidence = $this->calculateCurriculumFactorConfidence($teacher);

        // Normalize to ensure total doesn't exceed 100
        $total = $examFactorConfidence + $teacherFactorConfidence + $studentFactorConfidence + $curriculumFactorConfidence;
        $normalizer = $total > 0 ? 100 / $total : 1;

        return [
            'exam_factor' => (int) round($examFactorConfidence * $normalizer),
            'teacher_factor' => (int) round($teacherFactorConfidence * $normalizer),
            'student_factor' => (int) round($studentFactorConfidence * $normalizer),
            'curriculum_factor' => (int) round($curriculumFactorConfidence * $normalizer),
            'primary_concern' => $this->getPrimaryConcern($examFactorConfidence, $teacherFactorConfidence, $studentFactorConfidence, $curriculumFactorConfidence),
        ];
    }

    /**
     * Calculate exam factor confidence (0-100).
     * 
     * High confidence if:
     * - Many difficult questions (indicated by low pass rates across multiple teachers)
     * - High failure distribution across multiple sections
     * - Failures occur across multiple teachers teaching same subject
     */
    private function calculateExamFactorConfidence(Teacher $teacher, array $summary): float
    {
        $confidence = 0;

        // Check if prelim/midterm has notably lower pass rate than later exams
        $prelimTotal = $summary['Prelim']['total_students'] ?? 0;
        $prelimPass = $summary['Prelim']['pass_rate'] ?? 0;

        if ($prelimTotal > 0 && $prelimPass < 60) {
            $confidence += 25; // Early low performance suggests exam difficulty
        }

        // Check if failure is distributed across exam types
        $summaryWithResults = collect($summary)
            ->filter(fn ($item) => ($item['total_students'] ?? 0) > 0);

        if ($summaryWithResults->isEmpty()) {
            return 0;
        }

        $failureCounts = $summaryWithResults->pluck('failed_students')->all();
        $failureVariation = max($failureCounts) - min($failureCounts);
        
        if ($failureVariation < 5) {
            $confidence += 20; // Consistent failures suggest exam difficulty
        }

        // Check if many exams have low pass rates
        $lowPassRateCount = $summaryWithResults
            ->filter(fn($item) => $item['pass_rate'] < 70)
            ->count();

        if ($lowPassRateCount >= 2) {
            $confidence += 30; // Multiple exams with low pass rate
        }

        return min(100, $confidence);
    }

    /**
     * Calculate teacher factor confidence (0-100).
     * 
     * High confidence if:
     * - Failures concentrated under one teacher
     * - Other teachers teaching same subject perform better
     * - One subject notably worse than others taught by this teacher
     */
    private function calculateTeacherFactorConfidence(Teacher $teacher, array $subjectBreakdown): float
    {
        $confidence = 0;

        if (empty($subjectBreakdown)) {
            return 0;
        }

        // Check if one subject is notably worse
        $subjectsWithResults = collect($subjectBreakdown)
            ->filter(fn ($item) => ($item['total_results'] ?? 0) > 0)
            ->values();

        if ($subjectsWithResults->isEmpty()) {
            return 0;
        }

        $passRates = $subjectsWithResults->pluck('pass_rate')->all();
        $minPassRate = min($passRates);
        $maxPassRate = max($passRates);
        $variation = $maxPassRate - $minPassRate;

        if ($variation > 25) {
            $confidence += 35; // Large variation suggests instructional issue in one area
        }

        // Check if pass rates are consistently low
        $lowRateCount = $subjectsWithResults
            ->filter(fn($item) => $item['pass_rate'] < 70)
            ->count();

        if ($lowRateCount > 0 && $subjectsWithResults->count() > 1) {
            $confidence += 25; // Some subjects low while others are better
        }

        // Check for high failure count relative to students
        $failureRatio = 0;
        foreach ($subjectsWithResults as $subject) {
            $ratio = $subject['total_results'] > 0
                ? $subject['failed_students'] / $subject['total_results']
                : 0;
            $failureRatio = max($failureRatio, $ratio);
        }

        if ($failureRatio > 0.3) {
            $confidence += 20; // High failure ratio
        }

        return min(100, $confidence);
    }

    /**
     * Calculate student factor confidence (0-100).
     * 
     * High confidence if:
     * - Pattern of low participation or attendance
     * - Multiple subjects affected equally (not isolated to one teacher)
     * - Consistent low performance across exam types
     */
    private function calculateStudentFactorConfidence(Teacher $teacher): float
    {
        $confidence = 0;

        // This would require attendance/participation tracking
        // For now, return moderate baseline confidence if students are failing

        $exams = $teacher->teacherSubjects()
            ->with('exams.examResults')
            ->get();

        $totalStudents = 0;
        $failCount = 0;

        $exams->each(function ($ts) use (&$totalStudents, &$failCount) {
            $ts->exams->each(function ($exam) use (&$totalStudents, &$failCount) {
                $totalStudents += $exam->examResults->count();
                $failCount += $exam->examResults->where('remark', 'fail')->count();
            });
        });

        $failureRate = $totalStudents > 0 ? $failCount / $totalStudents : 0;

        if ($failureRate > 0.4) {
            $confidence = 35; // High failure rate could indicate student preparation
        }

        return min(100, $confidence);
    }

    /**
     * Calculate curriculum factor confidence (0-100).
     * 
     * High confidence if:
     * - Repeated weak areas in same competencies
     * - Multiple teachers affected equally
     * - Recurring yearly patterns
     */
    private function calculateCurriculumFactorConfidence(Teacher $teacher): float
    {
        $confidence = 0;

        // Check if teacher's subjects have consistently low performance
        $subjectBreakdown = (new PerformanceCalculator())->getTeacherSubjectBreakdown($teacher);

        $subjectsWithResults = collect($subjectBreakdown)
            ->filter(fn ($item) => ($item['total_results'] ?? 0) > 0);

        $allLow = $subjectsWithResults
            ->every(fn($item) => $item['pass_rate'] < 75);

        if ($allLow && $subjectsWithResults->isNotEmpty()) {
            $confidence += 40; // Across-the-board low performance suggests curriculum
        }

        return min(100, $confidence);
    }

    /**
     * Determine primary concern based on confidence scores.
     */
    private function getPrimaryConcern(float $examFactorConf, float $teacherFactorConf, float $studentFactorConf, float $curriculumFactorConf): string
    {
        $factors = [
            'exam' => $examFactorConf,
            'teacher' => $teacherFactorConf,
            'student' => $studentFactorConf,
            'curriculum' => $curriculumFactorConf,
        ];

        $primary = array_key_first(array_filter($factors, fn($v) => $v === max($factors)));

        return match ($primary) {
            'exam' => 'possible_exam_factor',
            'teacher' => 'possible_teacher_factor',
            'student' => 'possible_student_factor',
            'curriculum' => 'possible_curriculum_factor',
            default => 'insufficient_data',
        };
    }

    /**
     * Get human-readable analysis summary.
     */
    public function getAnalysisSummary(Teacher $teacher): array
    {
        $analysis = $this->analyzeTeacherPerformance($teacher);

        $summaries = [];

        if ($analysis['exam_factor'] >= 40) {
            $summaries[] = "Exam assessment indicators suggest possible examination difficulty or construction concerns in student performance patterns.";
        }

        if ($analysis['teacher_factor'] >= 40) {
            $summaries[] = "Performance variation across subjects indicates a possible instructional delivery concern requiring further assessment and reinforcement strategies.";
        }

        if ($analysis['student_factor'] >= 40) {
            $summaries[] = "Student preparation and engagement levels may be contributing factors to observed performance outcomes.";
        }

        if ($analysis['curriculum_factor'] >= 40) {
            $summaries[] = "Across-the-board performance patterns suggest a possible curriculum alignment concern requiring review and adjustment.";
        }

        if (empty($summaries)) {
            $summaries[] = "Overall performance data requires additional context for comprehensive diagnostic analysis.";
        }

        return [
            'factors' => $analysis,
            'summaries' => $summaries,
        ];
    }
}
