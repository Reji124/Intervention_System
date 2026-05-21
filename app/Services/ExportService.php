<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Department;
use App\Models\Semester;
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
    public function exportTeacherPDF(Teacher $teacher, ?Semester $semester = null)
    {
        $performanceCalc = new PerformanceCalculator();
        $factorAnalysis = new SubjectFactorAnalysisService();
        $reportGenerator = new ReportGenerator();

        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher, $semester);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher, $semester);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher, $semester);
        $narrative = $reportGenerator->generateTeacherNarrative($teacher, $semester);

        $data = [
            'teacher' => $teacher,
            'summary' => $summary,
            'overall' => $overall,
            'subjectBreakdown' => $subjectBreakdown,
            'narrative' => $narrative,
            'exportedAt' => now(),
            'generatedBy' => auth()->user()?->name ?? 'System',
        ] + $this->getReportHeaderData($semester);

        $pdf = Pdf::loadView('admin.analytics.exports.teacher-pdf', $data);
        $filename = "teacher_report_{$teacher->teacher_code}_{$this->formatSemesterCode($semester)}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export teacher report as Excel CSV.
     * 
     * Returns CSV file download response.
     */
    public function exportTeacherCSV(Teacher $teacher, ?Semester $semester = null)
    {
        $performanceCalc = new PerformanceCalculator();
        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher, $semester);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher, $semester);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher, $semester);

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
        $csv[] = ['Exam Type', 'Pass Rate', 'Failed Students', 'Mean Score', 'Remark'];
        foreach ($summary as $examType => $metrics) {
            $hasExamData = $metrics['total_students'] > 0;
            $csv[] = [
                $examType,
                $hasExamData ? $metrics['pass_rate'] . '%' : 'No data',
                $metrics['failed_students'],
                $hasExamData ? $metrics['mean_score'] : 'No data',
                $metrics['remark'] ?? 'N/A',
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

        return $this->downloadCSV($csv, "teacher_report_{$teacher->teacher_code}_{$this->formatSemesterCode($semester)}.csv");
    }

    /**
     * Export department report as PDF.
     */
    public function exportDepartmentPDF(Department $department, ?Semester $semester = null)
    {
        $performanceCalc = new PerformanceCalculator();
        $reportGenerator = new ReportGenerator();

        $metrics = $performanceCalc->getDepartmentMetrics($department, $semester);
        $narrative = $reportGenerator->generateDepartmentNarrative($department, $semester);

        // Get all teachers in department (for selected semester)
        $teacherSubjects = $department->courses()
            ->with([
                'subjects.teacherSubjects' => function ($q) use ($semester) {
                    if ($semester) {
                        $q->where('semester_id', $semester->id);
                    }
                    $q->with(['exams.examResults', 'teacher']);
                },
            ])
            ->get()
            ->flatMap(fn($course) => $course->subjects)
            ->flatMap(fn($subject) => $subject->teacherSubjects);

        $teachers = $teacherSubjects
            ->groupBy('teacher_id')
            ->map(function ($tsList) use ($performanceCalc, $semester) {
                $teacher = $tsList->first()->teacher;
                $teacherMetrics = $performanceCalc->getTeacherOverallMetrics($teacher, $semester);

                return [
                    'name' => $teacher->teacher_name,
                    'pass_rate' => $teacherMetrics['pass_rate'],
                    'failed_students' => $teacherMetrics['failed_students'],
                    'total_students' => $teacherMetrics['total_students'],
                    'remark' => $teacherMetrics['remark'],
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
        ] + $this->getReportHeaderData($semester);

        $pdf = Pdf::loadView('admin.analytics.exports.department-pdf', $data);
        $filename = "department_report_{$department->id}_{$this->formatSemesterCode($semester)}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export department report as CSV.
     */
    public function exportDepartmentCSV(Department $department, ?Semester $semester = null)
    {
        $performanceCalc = new PerformanceCalculator();
        $metrics = $performanceCalc->getDepartmentMetrics($department, $semester);

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
        $csv[] = ['Rank', 'Teacher Name', 'Pass Rate', 'Students', 'Remark'];

        $teacherSubjects = $department->courses()
            ->with([
                'subjects.teacherSubjects' => function ($q) use ($semester) {
                    if ($semester) {
                        $q->where('semester_id', $semester->id);
                    }
                    $q->with(['exams.examResults', 'teacher']);
                },
            ])
            ->get()
            ->flatMap(fn($course) => $course->subjects)
            ->flatMap(fn($subject) => $subject->teacherSubjects);

        $teachers = $teacherSubjects
            ->groupBy('teacher_id')
            ->map(function ($tsList) use ($performanceCalc, $semester) {
                $teacher = $tsList->first()->teacher;
                $teacherMetrics = $performanceCalc->getTeacherOverallMetrics($teacher, $semester);
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->teacher_name,
                    'pass_rate' => $teacherMetrics['pass_rate'],
                    'total_students' => $teacherMetrics['total_students'],
                    'remark' => $teacherMetrics['remark'],
                ];
            })
            ->sortByDesc('pass_rate')
            ->values();

        $rank = 1;
        foreach ($teachers as $teacher) {
            $csv[] = [
                $rank++,
                $teacher['name'],
                $teacher['total_students'] > 0 ? $teacher['pass_rate'] . '%' : 'No data',
                $teacher['total_students'],
                $teacher['remark'],
            ];
        }

        return $this->downloadCSV($csv, "department_report_{$department->id}_{$this->formatSemesterCode($semester)}.csv");
    }

    /**
     * Prepare data for print layout (returns view).
     */
    public function getPrintableTeacherReport(Teacher $teacher, ?Semester $semester = null)
    {
        $performanceCalc = new PerformanceCalculator();
        $factorAnalysis = new SubjectFactorAnalysisService();
        $reportGenerator = new ReportGenerator();

        $summary = $performanceCalc->getTeacherPerformanceSummary($teacher, $semester);
        $overall = $performanceCalc->getTeacherOverallMetrics($teacher, $semester);
        $subjectBreakdown = $performanceCalc->getTeacherSubjectBreakdown($teacher, $semester);
        $narrative = $reportGenerator->generateTeacherNarrative($teacher, $semester);

        return view('admin.analytics.exports.teacher-print', [
            'teacher' => $teacher,
            'summary' => $summary,
            'overall' => $overall,
            'subjectBreakdown' => $subjectBreakdown,
            'narrative' => $narrative,
            'exportedAt' => now(),
            'generatedBy' => auth()->user()?->name ?? 'System',
        ] + $this->getReportHeaderData($semester));
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
     * Format semester code for file naming.
     */
    private function formatSemesterCode(?Semester $semester): string
    {
        $sem = $semester ?? $this->getCurrentSemester();
        if (!$sem || !$sem->schoolYear) {
            return date('YmdHis');
        }

        $year = $sem->schoolYear->year_start;
        $semName = str_replace(' ', '', $sem->semester_name);

        return "{$year}_{$semName}";
    }

    /**
     * Shared report header data for PDF and print exports.
     */
    private function getReportHeaderData(?Semester $semester = null): array
    {
        $currentSemester = $semester ?? $this->getCurrentSemester();

        return [
            'currentSemester' => $currentSemester,
            'academicPeriod' => $this->formatAcademicPeriod($currentSemester),
            'reportLogoPath' => public_path('images/branding/hcdc_logo.png'),
            'reportLogoUrl' => asset('images/branding/hcdc_logo.png'),
        ];
    }

    private function getCurrentSemester(): ?Semester
    {
        return Semester::with('schoolYear')
            ->where('is_active', true)
            ->first();
    }

    private function formatAcademicPeriod(?Semester $semester): string
    {
        if (!$semester || !$semester->schoolYear) {
            return 'Academic Year & Semester Not Set';
        }

        return "Academic Year {$semester->schoolYear->year_start}-{$semester->schoolYear->year_end} - {$semester->semester_name}";
    }
}
