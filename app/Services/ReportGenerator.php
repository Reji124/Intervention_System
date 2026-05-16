<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Department;

/**
 * ReportGenerator
 * 
 * Generates professional institutional narratives based on analytics.
 * Produces 3-5 sentence summaries suitable for administrative review.
 * Uses professional, neutral language avoiding direct accusations.
 */
class ReportGenerator
{
    /**
     * Generate narrative report for a teacher.
     * 
     * Returns 3-5 sentences summarizing performance.
     */
    public function generateTeacherNarrative(Teacher $teacher): string
    {
        $performanceCalc = new PerformanceCalculator();
        $factorAnalysis = new FactorAnalysisService();

        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher);
        $analysis = $factorAnalysis->getAnalysisSummary($teacher);

        $sentences = [];

        // Opening: Overall performance trend
        $prelimRate = $summary['Prelim']['pass_rate'] ?? 0;
        $finalRate = $summary['Final']['pass_rate'] ?? 0;
        $overallRate = $overall['pass_rate'];

        if ($finalRate > $prelimRate) {
            $sentences[] = "Teacher performance demonstrates positive progression from preliminary to final examinations, indicating improved student mastery over the academic period.";
        } elseif ($finalRate < $prelimRate) {
            $sentences[] = "Examination results indicate declining student performance trajectories from preliminary to final assessments, suggesting areas for instructional reinforcement.";
        } else {
            $sentences[] = "Student performance remains relatively consistent across the academic period with an overall pass rate of {$overallRate}%.";
        }

        // Performance characterization
        if ($overallRate >= 85) {
            $sentences[] = "Overall achievement metrics reflect strong student mastery and effective instructional strategies.";
        } elseif ($overallRate >= 70) {
            $sentences[] = "Moderate achievement levels indicate students are acquiring core competencies, though additional instructional support may enhance mastery.";
        } else {
            $sentences[] = "Performance metrics indicate below-average achievement levels requiring comprehensive instructional review and intervention strategies.";
        }

        // Factor analysis insights (add one from analysis summaries)
        if (!empty($analysis['summaries'])) {
            $sentences[] = $analysis['summaries'][0];
        }

        // Recommendations
        if ($overallRate < 75) {
            $sentences[] = "Recommended actions include targeted remediation, formative assessment adjustments, and comprehensive instructional review to address identified performance gaps.";
        } else {
            $sentences[] = "Continued monitoring and targeted support for identified at-risk student populations is advised to maintain or improve current achievement levels.";
        }

        // Ensure 3-5 sentences
        $sentences = array_slice($sentences, 0, 5);

        return implode(" ", $sentences);
    }

    /**
     * Generate narrative report for a department.
     * 
     * Returns 3-5 sentences summarizing departmental performance.
     */
    public function generateDepartmentNarrative(Department $department): string
    {
        $performanceCalc = new PerformanceCalculator();
        $deptMetrics = $performanceCalc->getDepartmentMetrics($department);

        $sentences = [];

        $passRate = $deptMetrics['pass_rate'];
        $teacherCount = $deptMetrics['total_teachers'];
        $studentCount = $deptMetrics['total_students'];

        // Opening: Department overview
        $sentences[] = "The {$department->department_name} department demonstrates an overall pass rate of {$passRate}% across {$teacherCount} faculty members and {$studentCount} students.";

        // Performance characterization
        if ($passRate >= 85) {
            $sentences[] = "Performance metrics indicate robust academic achievement and effective departmental instructional practices.";
        } elseif ($passRate >= 70) {
            $sentences[] = "Moderate departmental performance levels suggest that while core competencies are being acquired, targeted improvements in specific subject areas could enhance overall outcomes.";
        } else {
            $sentences[] = "Departmental performance metrics indicate below-target achievement levels, warranting comprehensive review of instructional strategies and curriculum alignment.";
        }

        // Identify at-risk areas
        if (isset($deptMetrics['highest_risk_subject'])) {
            $riskSubject = $deptMetrics['highest_risk_subject'];
            $sentences[] = "The {$riskSubject['subject_name']} subject area demonstrates elevated risk levels with {$riskSubject['pass_rate']}% pass rate, requiring targeted intervention.";
        }

        // Recommendations
        if ($passRate < 75) {
            $sentences[] = "Departmental leadership should prioritize comprehensive curriculum review, faculty development initiatives, and targeted student support interventions.";
        } else {
            $sentences[] = "Continued focus on maintaining achievement levels while systematically addressing identified at-risk areas is recommended.";
        }

        // Ensure 3-5 sentences
        $sentences = array_slice($sentences, 0, 5);

        return implode(" ", $sentences);
    }

    /**
     * Generate observation for exam difficulty.
     */
    public function generateExamObservation(string $examType, int $passRate): string
    {
        if ($passRate >= 85) {
            return "Examination results indicate appropriate difficulty calibration with strong student achievement.";
        } elseif ($passRate >= 70) {
            return "Assessment indicates moderate difficulty level with acceptable student mastery demonstration.";
        } else {
            return "Examination results suggest possible difficulty calibration concerns or assessment construction issues requiring review.";
        }
    }

    /**
     * Generate subject-specific observation.
     */
    public function generateSubjectObservation(string $subjectName, int $passRate, int $totalStudents): string
    {
        if ($totalStudents === 0) {
            return "Insufficient data available for {$subjectName}.";
        }

        if ($passRate >= 85) {
            return "{$subjectName} demonstrates strong student achievement and effective competency mastery across assessed students.";
        } elseif ($passRate >= 70) {
            return "{$subjectName} shows moderate achievement levels with room for targeted instructional reinforcement.";
        } else {
            return "{$subjectName} indicates below-average achievement levels requiring comprehensive instructional and assessment review.";
        }
    }

    /**
     * Generate professional summary for administrative remarks.
     */
    public function generateAdministrativeRemark(Teacher $teacher, array $overallMetrics): string
    {
        $passRate = $overallMetrics['pass_rate'];

        if ($passRate >= 85) {
            return "Performance metrics indicate strong instructional effectiveness. Recommend recognition and potential peer mentoring leadership opportunities.";
        } elseif ($passRate >= 70) {
            return "Performance metrics within acceptable range. Recommend continued professional development and targeted support in identified challenge areas.";
        } else {
            return "Performance metrics indicate need for comprehensive support. Recommend formal instructional review, professional development plan, and administrative mentoring.";
        }
    }

    /**
     * Generate title for report based on performance.
     */
    public function generateReportTitle(Teacher $teacher, int $passRate): string
    {
        $name = $teacher->teacher_name;

        if ($passRate >= 85) {
            return "{$name}: High Achievement Performance Report";
        } elseif ($passRate >= 70) {
            return "{$name}: Standard Performance Report";
        } else {
            return "{$name}: Below-Average Performance Review";
        }
    }
}
