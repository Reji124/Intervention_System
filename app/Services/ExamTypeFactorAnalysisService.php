<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Semester;

/**
 * ExamTypeFactorAnalysisService
 * 
 * Analyzes teaching performance factors per exam type.
 * Evaluates exam quality, teaching effectiveness, and student performance.
 */
class ExamTypeFactorAnalysisService
{
    /**
     * Analyze factors for a specific exam type.
     * 
     * Returns array with exam_factor, teacher_factor, student_factor (0-100 each, sum=100)
     * Plus summary sentences for each factor.
     */
    public function analyzeExamTypePerformance(Teacher $teacher, string $examType, ?Semester $semester = null): array
    {
        $teacherSubjects = $teacher->teacherSubjects();
        
        if ($semester) {
            $teacherSubjects->where('semester_id', $semester->id);
        }
        
        $teacherSubjects = $teacherSubjects->with('exams.examResults')->get();

        // Filter exams by type
        $exams = $teacherSubjects
            ->flatMap(fn($ts) => $ts->exams)
            ->filter(fn($exam) => $exam->exam_type === $examType)
            ->values();

        if ($exams->count() === 0) {
            return $this->emptyAnalysis();
        }

        // Calculate metrics
        $totalResults = 0;
        $passCount = 0;
        $passRates = [];
        $itemMatrixData = [];

        $exams->each(function ($exam) use (&$totalResults, &$passCount, &$passRates, &$itemMatrixData) {
            $passCount += $exam->examResults->where('is_passed', true)->count();
            $totalResults += $exam->examResults->count();
            
            if ($exam->examResults->count() > 0) {
                $passRate = round(($exam->examResults->where('is_passed', true)->count() / $exam->examResults->count()) * 100, 1);
                $passRates[] = $passRate;
            }

            if (isset($exam->item_matrix_data) && is_array($exam->item_matrix_data)) {
                $itemMatrixData[] = $exam->item_matrix_data;
            }
        });

        $overallPassRate = $totalResults > 0 ? round(($passCount / $totalResults) * 100, 1) : 0;

        // Factor 1: Exam Quality (Item Matrix Analysis)
        $examFactor = $this->calculateExamQualityFactor($itemMatrixData);

        // Factor 2: Teaching Consistency (Student Performance Variance)
        $teacherFactor = $this->calculateTeachingConsistencyFactor($passRates);

        // Factor 3: Student Performance Level
        $studentFactor = $this->calculateStudentPerformanceFactor($overallPassRate);

        // Normalize to sum = 100
        $total = $examFactor + $teacherFactor + $studentFactor;
        if ($total > 0) {
            $examFactor = round(($examFactor / $total) * 100, 1);
            $teacherFactor = round(($teacherFactor / $total) * 100, 1);
            $studentFactor = round(($studentFactor / $total) * 100, 1);
        }

        return [
            'exam_factor' => $examFactor,
            'teacher_factor' => $teacherFactor,
            'student_factor' => $studentFactor,
            'summaries' => $this->generateSummaries([
                'exam_factor' => $examFactor,
                'teacher_factor' => $teacherFactor,
                'student_factor' => $studentFactor,
                'pass_rate' => $overallPassRate,
                'pass_rates' => $passRates,
            ]),
        ];
    }

    /**
     * Analyze overall factors across all exam types for a teacher.
     */
    public function analyzeOverallPerformance(Teacher $teacher, ?Semester $semester = null): array
    {
        $teacherSubjects = $teacher->teacherSubjects();
        
        if ($semester) {
            $teacherSubjects->where('semester_id', $semester->id);
        }
        
        $teacherSubjects = $teacherSubjects->with('exams.examResults')->get();
        $exams = $teacherSubjects->flatMap(fn($ts) => $ts->exams)->values();

        if ($exams->count() === 0) {
            return $this->emptyAnalysis();
        }

        // Calculate overall metrics
        $totalResults = 0;
        $passCount = 0;
        $passRates = [];
        $itemMatrixData = [];

        $exams->each(function ($exam) use (&$totalResults, &$passCount, &$passRates, &$itemMatrixData) {
            $passCount += $exam->examResults->where('is_passed', true)->count();
            $totalResults += $exam->examResults->count();
            
            if ($exam->examResults->count() > 0) {
                $passRate = round(($exam->examResults->where('is_passed', true)->count() / $exam->examResults->count()) * 100, 1);
                $passRates[] = $passRate;
            }

            if (isset($exam->item_matrix_data) && is_array($exam->item_matrix_data)) {
                $itemMatrixData[] = $exam->item_matrix_data;
            }
        });

        $overallPassRate = $totalResults > 0 ? round(($passCount / $totalResults) * 100, 1) : 0;

        // Calculate factors
        $examFactor = $this->calculateExamQualityFactor($itemMatrixData);
        $teacherFactor = $this->calculateTeachingConsistencyFactor($passRates);
        $studentFactor = $this->calculateStudentPerformanceFactor($overallPassRate);

        // Normalize
        $total = $examFactor + $teacherFactor + $studentFactor;
        if ($total > 0) {
            $examFactor = round(($examFactor / $total) * 100, 1);
            $teacherFactor = round(($teacherFactor / $total) * 100, 1);
            $studentFactor = round(($studentFactor / $total) * 100, 1);
        }

        return [
            'exam_factor' => $examFactor,
            'teacher_factor' => $teacherFactor,
            'student_factor' => $studentFactor,
            'summaries' => $this->generateSummaries([
                'exam_factor' => $examFactor,
                'teacher_factor' => $teacherFactor,
                'student_factor' => $studentFactor,
                'pass_rate' => $overallPassRate,
                'pass_rates' => $passRates,
            ]),
        ];
    }

    private function calculateExamQualityFactor(array $itemMatrixDataList): float
    {
        if (empty($itemMatrixDataList)) {
            return 20;
        }

        $qualityScores = [];

        foreach ($itemMatrixDataList as $itemData) {
            $legend = $itemData['legend'] ?? [];
            
            // Handle both array and integer values from legend
            $rejectCount = is_array($legend['reject'] ?? null) ? count($legend['reject']) : (int)($legend['reject'] ?? 0);
            $revisionCount = is_array($legend['needs_revision'] ?? null) ? count($legend['needs_revision']) : (int)($legend['needs_revision'] ?? 0);
            $acceptableCount = is_array($legend['acceptable'] ?? null) ? count($legend['acceptable']) : (int)($legend['acceptable'] ?? 0);

            $total = $rejectCount + $revisionCount + $acceptableCount;
            if ($total === 0) {
                continue;
            }

            $rejectRatio = (float)$rejectCount / $total;
            
            // Quality score: higher acceptable ratio = better quality
            if ($rejectRatio > 0.4) {
                $qualityScores[] = 10; // Poor quality
            } elseif ($rejectRatio > 0.2) {
                $qualityScores[] = 20; // Fair quality
            } elseif ((float)$revisionCount / $total > 0.3) {
                $qualityScores[] = 30; // Needs work
            } else {
                $qualityScores[] = 40; // Good quality
            }
        }

        return !empty($qualityScores) ? array_sum($qualityScores) / count($qualityScores) : 20;
    }

    private function calculateTeachingConsistencyFactor(array $passRates): float
    {
        if (count($passRates) < 2) {
            return 25;
        }

        // Ensure all values are numeric
        $passRates = array_map(fn($x) => (float)$x, $passRates);
        
        $mean = array_sum($passRates) / count($passRates);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $passRates)) / count($passRates);
        $stdDev = sqrt($variance);

        // Low variance = consistent teaching = higher score
        if ($stdDev < 10) {
            return 35; // Very consistent
        } elseif ($stdDev < 20) {
            return 30; // Consistent
        } elseif ($stdDev < 35) {
            return 20; // Variable
        } else {
            return 15; // Highly inconsistent
        }
    }

    private function calculateStudentPerformanceFactor(float $passRate): float
    {
        // Direct mapping: higher pass rate = better student performance
        $passRate = (float)$passRate;
        
        if ($passRate >= 85) {
            return 40; // Excellent
        } elseif ($passRate >= 70) {
            return 30; // Good
        } elseif ($passRate >= 50) {
            return 20; // Fair
        } else {
            return 10; // Poor
        }
    }

    private function generateSummaries(array $data): array
    {
        $summaries = [];

        // Exam Factor summary
        if ($data['exam_factor'] > 35) {
            $summaries['exam_factor'] = 'Exam items are well-constructed with clear questions. Item quality is strong.';
        } elseif ($data['exam_factor'] > 25) {
            $summaries['exam_factor'] = 'Exam items are generally acceptable with minor revisions needed.';
        } else {
            $summaries['exam_factor'] = 'Exam items need improvement. Consider revising question clarity and accuracy.';
        }

        // Teacher Factor summary
        if ($data['teacher_factor'] > 32) {
            $summaries['teacher_factor'] = 'Teaching approach is highly consistent across all assessments.';
        } elseif ($data['teacher_factor'] > 22) {
            $summaries['teacher_factor'] = 'Teaching effectiveness is fairly consistent with minor variations.';
        } else {
            $summaries['teacher_factor'] = 'Teaching delivery varies across assessments. Consider standardizing approach.';
        }

        // Student Factor summary
        $passRate = $data['pass_rate'];
        if ($passRate >= 85) {
            $summaries['student_factor'] = 'Students demonstrate excellent mastery of learning objectives.';
        } elseif ($passRate >= 70) {
            $summaries['student_factor'] = 'Students show good understanding with room for improvement.';
        } elseif ($passRate >= 50) {
            $summaries['student_factor'] = 'Students are meeting minimum standards but need additional support.';
        } else {
            $summaries['student_factor'] = 'Students require intensive intervention to meet learning standards.';
        }

        return $summaries;
    }

    private function emptyAnalysis(): array
    {
        return [
            'exam_factor' => 0,
            'teacher_factor' => 0,
            'student_factor' => 0,
            'summaries' => [
                'exam_factor' => 'No data available.',
                'teacher_factor' => 'No data available.',
                'student_factor' => 'No data available.',
            ],
        ];
    }
}
