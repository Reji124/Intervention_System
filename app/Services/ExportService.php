<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

/**
 * ExportService
 * 
 * Handles export of reports in multiple formats: PDF, Excel, Print
 * Manages data preparation and template rendering for professional exports.
 */
class ExportService
{
    /**
     * Export teacher report as PDF.
     * 
     * Returns PDF file download response.
     */
    public function exportTeacherPDF(Teacher $teacher)
    {
        $performanceCalc = new PerformanceCalculator();
        $factorAnalysis = new FactorAnalysisService();
        $reportGenerator = new ReportGenerator();

        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher);
        $analysis = $factorAnalysis->getAnalysisSummary($teacher);
        $narrative = $reportGenerator->generateTeacherNarrative($teacher);

        $data = [
            'teacher' => $teacher,
            'summary' => $summary,
            'overall' => $overall,
            'subjectBreakdown' => $subjectBreakdown,
            'analysis' => $analysis,
            'narrative' => $narrative,
            'exportedAt' => now(),
            'generatedBy' => auth()->user()?->name ?? 'System',
        ];

        $pdf = Pdf::loadView('admin.analytics.exports.teacher-pdf', $data);
        $filename = "teacher_report_{$teacher->teacher_code}_{$this->getCurrentSemesterCode()}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export teacher report as Excel CSV.
     * 
     * Returns CSV file download response.
     */
    public function exportTeacherCSV(Teacher $teacher)
    {
        $performanceCalc = new PerformanceCalculator();
        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher);

        // Build CSV content
        $csv = [];
        $csv[] = ['Teacher Report Export', '', '', '', ''];
        $csv[] = ['Teacher Name:', $teacher->teacher_name, '', '', ''];
        $csv[] = ['Teacher Code:', $teacher->teacher_code ?? 'N/A', '', '', ''];
        $csv[] = ['Department:', $teacher->teacherSubjects->first()?->subject->department->department_name ?? 'N/A', '', '', ''];
        $csv[] = [];

        // Overall metrics
        $csv[] = ['Overall Performance Summary', '', '', '', ''];
        $csv[] = ['Pass Rate', 'Failure Rate', 'Failed Students', 'Mean Score', 'Total Students'];
        $hasOverallData = $overall['total_students'] > 0;
        $csv[] = [
            $hasOverallData ? $overall['pass_rate'] . '%' : 'No data',
            $hasOverallData ? $overall['failure_rate'] . '%' : 'No data',
            $overall['failed_students'],
            $hasOverallData ? $overall['mean_score'] : 'No data',
            $overall['total_students'],
        ];
        $csv[] = [];

        // Exam type summary
        $csv[] = ['Exam Type Performance', '', '', '', ''];
        $csv[] = ['Exam Type', 'Pass Rate', 'Failed Students', 'Mean Score', 'Difficulty'];
        foreach ($summary as $examType => $metrics) {
            $hasExamData = $metrics['total_students'] > 0;
            $csv[] = [
                $examType,
                $hasExamData ? $metrics['pass_rate'] . '%' : 'No data',
                $metrics['failed_students'],
                $hasExamData ? $metrics['mean_score'] : 'No data',
                $metrics['difficulty'],
            ];
        }
        $csv[] = [];

        // Subject breakdown
        $csv[] = ['Subject Performance Breakdown', '', '', '', ''];
        $csv[] = ['Subject', 'Code', 'Pass Rate', 'Failed Students', 'Intervention Count'];
        foreach ($subjectBreakdown as $subject) {
            $hasSubjectData = ($subject['total_results'] ?? 0) > 0;
            $csv[] = [
                $subject['subject_name'],
                $subject['subject_code'],
                $hasSubjectData ? $subject['pass_rate'] . '%' : 'No data',
                $subject['failed_students'],
                $subject['intervention_count'],
            ];
        }

        return $this->downloadCSV($csv, "teacher_report_{$teacher->teacher_code}_{$this->getCurrentSemesterCode()}.csv");
    }

    /**
     * Export department report as PDF.
     */
    public function exportDepartmentPDF(Department $department)
    {
        $performanceCalc = new PerformanceCalculator();
        $reportGenerator = new ReportGenerator();

        $metrics = $performanceCalc->getDepartmentMetrics($department);
        $narrative = $reportGenerator->generateDepartmentNarrative($department);

        // Get all teachers in department
        $teachers = $department->courses()
            ->with('subjects.teacherSubjects.teacher.exams.examResults')
            ->get()
            ->flatMap(function ($course) {
                return $course->subjects->flatMap(function ($subject) {
                    return $subject->teacherSubjects->pluck('teacher');
                });
            })
            ->unique('id')
            ->map(function ($teacher) use ($performanceCalc) {
                $teacherMetrics = $performanceCalc->getTeacherOverallMetrics($teacher);
                $riskLevel = AnalyticsService::getRiskLevel(
                    $teacherMetrics['pass_rate'],
                    $teacherMetrics['total_students']
                );

                return [
                    'name' => $teacher->teacher_name,
                    'pass_rate' => $teacherMetrics['pass_rate'],
                    'failed_students' => $teacherMetrics['failed_students'],
                    'total_students' => $teacherMetrics['total_students'],
                    'risk_level' => $riskLevel['level'],
                    'risk_label' => $riskLevel['label'],
                ];
            })
            ->sortByDesc('pass_rate');

        $data = [
            'department' => $department,
            'metrics' => $metrics,
            'teachers' => $teachers,
            'narrative' => $narrative,
            'exportedAt' => now(),
            'generatedBy' => auth()->user()?->name ?? 'System',
        ];

        $pdf = Pdf::loadView('admin.analytics.exports.department-pdf', $data);
        $filename = "department_report_{$department->id}_{$this->getCurrentSemesterCode()}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export department report as CSV.
     */
    public function exportDepartmentCSV(Department $department)
    {
        $performanceCalc = new PerformanceCalculator();
        $metrics = $performanceCalc->getDepartmentMetrics($department);

        $csv = [];
        $csv[] = ['Department Report Export', '', '', '', ''];
        $csv[] = ['Department Name:', $department->department_name, '', '', ''];
        $csv[] = [];

        // Department summary
        $csv[] = ['Department Summary', '', '', '', ''];
        $csv[] = ['Pass Rate', 'Total Teachers', 'Total Students', '', ''];
        $csv[] = [
            $metrics['total_students'] > 0 ? $metrics['pass_rate'] . '%' : 'No data',
            $metrics['total_teachers'],
            $metrics['total_students'],
            '',
            '',
        ];
        $csv[] = [];

        // Teacher rankings
        $csv[] = ['Teacher Performance Rankings', '', '', '', ''];
        $csv[] = ['Rank', 'Teacher Name', 'Pass Rate', 'Students', ''];

        $teachers = $department->courses()
            ->with('subjects.teacherSubjects.teacher.exams.examResults')
            ->get()
            ->flatMap(function ($course) {
                return $course->subjects->flatMap(function ($subject) {
                    return $subject->teacherSubjects->pluck('teacher');
                });
            })
            ->unique('id');

        $rank = 1;
        foreach ($teachers as $teacher) {
            $teacherMetrics = $performanceCalc->getTeacherOverallMetrics($teacher);
            $csv[] = [
                $rank++,
                $teacher->teacher_name,
                $teacherMetrics['total_students'] > 0 ? $teacherMetrics['pass_rate'] . '%' : 'No data',
                $teacherMetrics['total_students'],
                '',
            ];
        }

        return $this->downloadCSV($csv, "department_report_{$department->id}_{$this->getCurrentSemesterCode()}.csv");
    }

    /**
     * Prepare data for print layout (returns view).
     */
    public function getPrintableTeacherReport(Teacher $teacher)
    {
        $performanceCalc = new PerformanceCalculator();
        $factorAnalysis = new FactorAnalysisService();
        $reportGenerator = new ReportGenerator();

        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher);
        $analysis = $factorAnalysis->getAnalysisSummary($teacher);
        $narrative = $reportGenerator->generateTeacherNarrative($teacher);

        return view('admin.analytics.exports.teacher-print', [
            'teacher' => $teacher,
            'summary' => $summary,
            'overall' => $overall,
            'subjectBreakdown' => $subjectBreakdown,
            'analysis' => $analysis,
            'narrative' => $narrative,
            'exportedAt' => now(),
            'generatedBy' => auth()->user()?->name ?? 'System',
        ]);
    }

    /**
     * Download CSV file.
     */
    private function downloadCSV(array $csv, string $filename)
    {
        $callback = function () use ($csv) {
            $file = fopen('php://output', 'w');
            foreach ($csv as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get current semester code for file naming.
     */
    private function getCurrentSemesterCode(): string
    {
        $semester = \App\Models\Semester::where('is_active', true)->first();
        if (!$semester || !$semester->schoolYear) {
            return date('YmdHis');
        }

        $year = $semester->schoolYear->year_start;
        $sem = str_replace(' ', '', $semester->semester_name);

        return "{$year}_{$sem}";
    }
}
