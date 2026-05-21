<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Department;
use App\Models\Semester;

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
     * Optionally filters by semester.
     * 
     * Returns 3-5 sentences summarizing performance.
     */
    public function generateTeacherNarrative(Teacher $teacher, ?Semester $semester = null): string
    {
        $performanceCalc = new PerformanceCalculator();
        $remarkCalc = new RemarkCalculator();

        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher, $semester);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher, $semester);

        $sentences = [];

        // Opening: Overall performance trend
        $prelimRate = $summary['Prelim']['pass_rate'] ?? 0;
        $midtermRate = $summary['Midterm']['pass_rate'] ?? 0;
        $prefinalRate = $summary['Prefinal']['pass_rate'] ?? 0;
        $finalRate = $summary['Final']['pass_rate'] ?? 0;
        $overallRate = $overall['pass_rate'];

        if ($overall['total_students'] <= 0) {
            return "No uploaded exam results are available yet for {$teacher->teacher_name}. Analytics and risk interpretation will begin once exam results are uploaded.";
        }

        // Exam type progression analysis
        if ($finalRate > $prelimRate && $finalRate > $prelimRate + 5) {
            $sentences[] = "Teacher performance demonstrates positive progression from preliminary to final examinations, indicating improved student mastery over the academic period.";
        } elseif ($finalRate < $prelimRate && $prelimRate > $finalRate + 5) {
            $sentences[] = "Examination results indicate declining student performance trajectories from preliminary to final assessments, suggesting areas for instructional reinforcement.";
        } else {
            $sentences[] = "Student performance remains relatively consistent across examination periods.";
        }

        // Per-exam-type statements
        if ($prelimRate > 0) {
            $prelimRemark = $remarkCalc->getRemarkLabel($prelimRate);
            $sentences[] = "Preliminary examination performance shows {$prelimRemark} results with {$prelimRate}% pass rate.";
        }

        if ($finalRate > 0 && abs($finalRate - $prelimRate) > 10) {
            $finalRemark = $remarkCalc->getRemarkLabel($finalRate);
            $sentences[] = "Final examination performance demonstrates {$finalRemark} results with {$finalRate}% pass rate.";
        }

        // Overall performance characterization with remark
        $overallRemark = $overall['remark'];
        if ($overallRate >= 85) {
            $sentences[] = "Overall achievement metrics reflect strong student mastery and effective instructional strategies ({$overallRemark} performance).";
        } elseif ($overallRate >= 70) {
            $sentences[] = "Moderate achievement levels indicate students are acquiring core competencies, though additional instructional support may enhance mastery ({$overallRemark} performance).";
        } else {
            $sentences[] = "Performance metrics indicate below-average achievement levels requiring comprehensive instructional review and intervention strategies ({$overallRemark} performance).";
        }

        // Ensure 3-5 sentences, trim if necessary
        $sentences = array_filter($sentences);
        $sentences = array_slice($sentences, 0, 5);

        return implode(" ", $sentences);
    }

    /**
     * Generate narrative report for a department.
     * Optionally filters by semester.
     * 
     * Returns 3-5 sentences summarizing departmental performance.
     */
    public function generateDepartmentNarrative(Department $department, ?Semester $semester = null): string
    {
        $performanceCalc = new PerformanceCalculator();
        $deptMetrics = $performanceCalc->getDepartmentMetrics($department, $semester);

        $sentences = [];

        $passRate = $deptMetrics['pass_rate'];
        $remark = $deptMetrics['remark'];
        $teacherCount = $deptMetrics['total_teachers'];
        $studentCount = $deptMetrics['total_students'];

        if ($studentCount <= 0) {
            return "No uploaded exam results are available yet for the {$department->department_name} department. Department analytics and risk interpretation will begin once exam results are uploaded.";
        }

        // Opening: Department overview with remark
        $sentences[] = "The {$department->department_name} department demonstrates an overall pass rate of {$passRate}% ({$remark} performance) across {$teacherCount} faculty members and {$studentCount} students.";

        // Performance characterization
        if ($passRate >= 85) {
            $sentences[] = "Performance metrics indicate robust academic achievement and effective departmental instructional practices.";
        } elseif ($passRate >= 70) {
            $sentences[] = "Moderate departmental performance levels suggest that while core competencies are being acquired, targeted improvements in specific subject areas could enhance overall outcomes.";
        } else {
            $sentences[] = "Departmental performance metrics indicate below-target achievement levels, warranting comprehensive review of instructional strategies and curriculum alignment.";
        }

        // Identify at-risk areas
        if (isset($deptMetrics['highest_risk_subject']) && ($deptMetrics['highest_risk_subject']['total_results'] ?? 0) > 0) {
            $riskSubject = $deptMetrics['highest_risk_subject'];
            $subjectRemark = $riskSubject['remark'] ?? 'low';
            $sentences[] = "The {$riskSubject['subject_name']} subject area demonstrates elevated risk levels with {$riskSubject['pass_rate']}% pass rate ({$subjectRemark} performance), requiring targeted intervention.";
        }

        // Recommendations
        if ($passRate < 75) {
            $sentences[] = "Departmental leadership should prioritize comprehensive curriculum review, faculty development initiatives, and targeted student support interventions.";
        } else {
            $sentences[] = "Continued focus on maintaining achievement levels while systematically addressing identified at-risk areas is recommended.";
        }

        // Ensure 3-5 sentences
        $sentences = array_filter($sentences);
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

        if (($overallMetrics['total_students'] ?? 0) <= 0) {
            return "No uploaded exam results are available yet. Review is pending until assessment data has been uploaded.";
        }

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
