<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Support\Collection;

/**
 * SubjectFactorAnalysisService
 * 
 * Analyzes performance factors for a teacher's specific subject:
 * - Exam Factor: Quality of exam questions (reject vs acceptable items)
 * - Teacher Factor: Instructional effectiveness across sections
 * - Student Factor: Student preparedness and engagement
 * 
 * Returns confidence percentages (0-100) for each factor.
 */
class SubjectFactorAnalysisService
{
    /**
     * Analyze performance of a teacher in a specific subject for a semester.
     * 
     * Returns array with factors and confidence ratings.
     */
    public function analyzeSubjectPerformance(
        Teacher $teacher,
        Subject $subject,
        Semester $semester
    ): array {
        $examFactor = $this->calculateExamFactor($teacher, $subject, $semester);
        $teacherFactor = $this->calculateTeacherFactor($teacher, $subject, $semester);
        $studentFactor = $this->calculateStudentFactor($teacher, $subject, $semester);

        // Normalize to ensure total equals 100
        $total = $examFactor + $teacherFactor + $studentFactor;
        $normalizer = $total > 0 ? 100 / $total : 1;

        return [
            'exam_factor' => (int) round($examFactor * $normalizer),
            'teacher_factor' => (int) round($teacherFactor * $normalizer),
            'student_factor' => (int) round($studentFactor * $normalizer),
            'summaries' => $this->generateSummaries(
                $examFactor * $normalizer,
                $teacherFactor * $normalizer,
                $studentFactor * $normalizer
            ),
        ];
    }

    /**
     * Calculate exam factor confidence (0-100).
     * 
     * High confidence if:
     * - Many reject questions (poor exam construction)
     * - High proportion of reject-to-acceptable ratio
     * - Consistent rejection pattern across multiple exams
     */
    private function calculateExamFactor(Teacher $teacher, Subject $subject, Semester $semester): float
    {
        $confidence = 0;

        // Get all exams for this teacher-subject-semester combination
        $exams = $teacher->teacherSubjects()
            ->where('subject_id', $subject->id)
            ->whereHas('semester', fn($q) => $q->where('id', $semester->id))
            ->with('exams.examResults')
            ->get()
            ->flatMap(fn($ts) => $ts->exams);

        if ($exams->isEmpty()) {
            return 0;
        }

        // Analyze item_matrix_data from exams
        $rejectCounts = [];
        $acceptableCounts = [];
        $examsWithMatrix = 0;

        foreach ($exams as $exam) {
            if (empty($exam->item_matrix_data)) {
                continue;
            }

            $examsWithMatrix++;
            $matrix = $exam->item_matrix_data;

            // Count reject and acceptable items from matrix
            $rejectCount = count($matrix['legend']['reject'] ?? []);
            $acceptableCount = count($matrix['legend']['acceptable'] ?? []);

            $rejectCounts[] = $rejectCount;
            $acceptableCounts[] = $acceptableCount;
        }

        if ($examsWithMatrix === 0) {
            return 0;
        }

        // Analyze rejection ratio
        $avgReject = array_sum($rejectCounts) / count($rejectCounts);
        $avgAcceptable = array_sum($acceptableCounts) / count($acceptableCounts);

        if ($avgAcceptable > 0) {
            $rejectRatio = $avgReject / ($avgReject + $avgAcceptable);

            // If reject items are > 30% of total, exam factor is significant
            if ($rejectRatio > 0.30) {
                $confidence += 40;
            } elseif ($rejectRatio > 0.20) {
                $confidence += 25;
            } else {
                $confidence += 10;
            }
        }

        // Check if all exams have similar reject patterns (systematic exam issue)
        if (count($rejectCounts) > 1) {
            $minReject = min($rejectCounts);
            $maxReject = max($rejectCounts);
            $variation = $maxReject > 0 ? ($maxReject - $minReject) / $maxReject : 0;

            if ($variation < 0.20) {
                // Consistent rejection pattern = systematic exam construction issue
                $confidence += 20;
            }
        }

        return min(100, $confidence);
    }

    /**
     * Calculate teacher factor confidence (0-100).
     * 
     * High confidence if:
     * - Large variation in pass rates across sections of same subject
     * - One section significantly underperforms vs others
     * - High pass rate variation suggests instructional differences
     */
    private function calculateTeacherFactor(Teacher $teacher, Subject $subject, Semester $semester): float
    {
        $confidence = 0;

        // Get all sections this teacher has for this subject-semester
        $teacherSubjects = $teacher->teacherSubjects()
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->with(['exams.examResults'])
            ->get();

        if ($teacherSubjects->isEmpty()) {
            return 0;
        }

        // Calculate pass rate for each section
        $sectionPassRates = [];
        foreach ($teacherSubjects as $ts) {
            $totalResults = 0;
            $passCount = 0;

            foreach ($ts->exams as $exam) {
                foreach ($exam->examResults as $result) {
                    $totalResults++;
                    if ($result->remark === 'pass') {
                        $passCount++;
                    }
                }
            }

            if ($totalResults > 0) {
                $passRate = ($passCount / $totalResults) * 100;
                $sectionPassRates[$ts->section] = $passRate;
            }
        }

        // Analyze section variation
        if (count($sectionPassRates) > 1) {
            $minRate = min($sectionPassRates);
            $maxRate = max($sectionPassRates);
            $variation = $maxRate - $minRate;

            // Large variation indicates instructional differences between sections
            if ($variation > 25) {
                $confidence += 45; // Significant instructional difference
            } elseif ($variation > 15) {
                $confidence += 30; // Moderate instructional difference
            } elseif ($variation > 5) {
                $confidence += 15; // Minor instructional difference
            } else {
                $confidence += 5;  // Consistent across sections
            }
        } else {
            // Only one section - can't compare instructional delivery
            $confidence += 5;
        }

        return min(100, $confidence);
    }

    /**
     * Calculate student factor confidence (0-100).
     * 
     * High confidence if:
     * - High proportion of students fail across multiple exams
     * - Consistent failure patterns for same students
     * - Student-level performance issues not exam/instruction related
     */
    private function calculateStudentFactor(Teacher $teacher, Subject $subject, Semester $semester): float
    {
        $confidence = 0;

        // Get all exam results for this teacher-subject-semester
        $examResults = $teacher->teacherSubjects()
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->with(['exams.examResults.student'])
            ->get()
            ->flatMap(fn($ts) => $ts->exams)
            ->flatMap(fn($exam) => $exam->examResults);

        if ($examResults->isEmpty()) {
            return 0;
        }

        // Group results by student to find patterns
        $studentFailureRates = $examResults->groupBy('student_id')
            ->map(function ($studentResults) {
                $total = $studentResults->count();
                $fails = $studentResults->where('remark', 'fail')->count();
                return $total > 0 ? ($fails / $total) * 100 : 0;
            });

        if ($studentFailureRates->isEmpty()) {
            return 0;
        }

        // Analyze student failure patterns
        $avgFailureRate = $studentFailureRates->average();
        $highFailureCount = $studentFailureRates->filter(fn($rate) => $rate > 50)->count();
        $persistentFailerCount = $studentFailureRates->filter(fn($rate) => $rate > 75)->count();

        // If many students consistently fail, it's a student preparation issue
        if ($highFailureCount > ($studentFailureRates->count() * 0.3)) {
            $confidence += 35; // >30% of students have >50% failure rate
        } elseif ($highFailureCount > ($studentFailureRates->count() * 0.15)) {
            $confidence += 20; // >15% of students have >50% failure rate
        } else {
            $confidence += 10;
        }

        // Persistent failers (>75% failure rate) indicate strong student factor
        if ($persistentFailerCount > 0) {
            $confidence += min(30, $persistentFailerCount * 10); // Up to 30 more
        }

        return min(100, $confidence);
    }

    /**
     * Generate summary sentences for each factor.
     */
    private function generateSummaries(float $examFactor, float $teacherFactor, float $studentFactor): array
    {
        $summaries = [];

        // Exam factor summary
        if ($examFactor > 45) {
            $summaries[] = "Exam quality appears to be a significant factor, with a notable proportion of poorly constructed or difficult questions affecting student performance.";
        } elseif ($examFactor > 30) {
            $summaries[] = "Exam construction shows some areas of concern that may be impacting performance outcomes.";
        }

        // Teacher factor summary
        if ($teacherFactor > 45) {
            $summaries[] = "Instructional delivery or teaching methodology shows notable variation, particularly when comparing different sections or student cohorts.";
        } elseif ($teacherFactor > 30) {
            $summaries[] = "Teaching approaches may benefit from review and standardization across different student sections.";
        }

        // Student factor summary
        if ($studentFactor > 45) {
            $summaries[] = "Student preparedness and engagement appear to be substantial contributing factors to overall performance outcomes.";
        } elseif ($studentFactor > 30) {
            $summaries[] = "Student-level factors, including preparedness and engagement, warrant attention and potential intervention.";
        }

        // Default if no major factors identified
        if (empty($summaries)) {
            $summaries[] = "Performance factors are relatively balanced across exam quality, instructional delivery, and student preparedness.";
        }

        return $summaries;
    }
}
