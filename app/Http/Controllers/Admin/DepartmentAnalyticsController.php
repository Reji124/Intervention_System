<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Semester;
use App\Services\PerformanceCalculator;
use App\Services\ReportGenerator;
use App\Services\ExportService;
use App\Services\AnalyticsSessionService;
use Illuminate\Http\Request;

/**
 * DepartmentAnalyticsController
 * 
 * Manages department-level analytics and reporting.
 * Displays departmental performance, teacher rankings, and trends.
 */
class DepartmentAnalyticsController extends Controller
{
    public function __construct(
        private PerformanceCalculator $performanceCalculator,
        private ReportGenerator $reportGenerator,
        private ExportService $exportService,
        private AnalyticsSessionService $sessionService
    ) {}

    /**
     * Display list of departments with aggregated analytics.
     */
    public function index(Request $request)
    {
        $selectedSemester = $this->sessionService->getSelectedSemester();
        $departments = Department::all();

        // Compute metrics for each department
        $departmentData = $departments->map(function ($dept) use ($selectedSemester) {
            $metrics = $this->performanceCalculator->getDepartmentMetrics($dept, $selectedSemester);
            $riskLevel = \App\Services\AnalyticsService::getRiskLevel(
                $metrics['pass_rate'],
                $metrics['total_students']
            );

            return [
                'id' => $dept->id,
                'name' => $dept->department_name,
                'pass_rate' => $metrics['pass_rate'],
                'remark' => $metrics['remark'],
                'total_teachers' => $metrics['total_teachers'],
                'total_students' => $metrics['total_students'],
                'risk_level' => $riskLevel['level'],
                'risk_label' => $riskLevel['label'],
            ];
        })->sortByDesc('pass_rate');

        return view('admin.analytics.departments.index', [
            'departments' => $departmentData,
            'currentSemester' => $selectedSemester,
            'activeTab' => 'departments',
        ]);
    }

    /**
     * Display detailed analytics for a single department.
     */
    public function show(Department $department)
    {
        $selectedSemester = $this->sessionService->getSelectedSemester();

        $metrics = $this->performanceCalculator->getDepartmentMetrics($department, $selectedSemester);
        $narrative = $this->reportGenerator->generateDepartmentNarrative($department, $selectedSemester);

        // Get all teachers with subjects in this department (for selected semester)
        $teacherSubjects = $department->courses()
            ->with([
                'subjects.teacherSubjects' => function ($q) use ($selectedSemester) {
                    $q->where('semester_id', $selectedSemester->id)
                      ->with(['exams.examResults', 'teacher']);
                },
            ])
            ->get()
            ->flatMap(fn($course) => $course->subjects)
            ->flatMap(fn($subject) => $subject->teacherSubjects);

        // Get unique teachers and calculate metrics
        $teachersData = $teacherSubjects
            ->groupBy('teacher_id')
            ->map(function ($tsList) use ($selectedSemester) {
                $teacher = $tsList->first()->teacher;
                $metrics = $this->performanceCalculator->getTeacherOverallMetrics($teacher, $selectedSemester);
                $riskLevel = \App\Services\AnalyticsService::getRiskLevel(
                    $metrics['pass_rate'],
                    $metrics['total_students']
                );

                return [
                    'id' => $teacher->id,
                    'name' => $teacher->teacher_name,
                    'code' => $teacher->teacher_code,
                    'pass_rate' => $metrics['pass_rate'],
                    'remark' => $metrics['remark'],
                    'failed_students' => $metrics['failed_students'],
                    'total_students' => $metrics['total_students'],
                    'risk_level' => $metrics['remark_class'],
                    'risk_label' => $metrics['remark'],
                ];
            })
            ->sortByDesc('pass_rate')
            ->values();

        return view('admin.analytics.departments.show', [
            'department' => $department,
            'metrics' => $metrics,
            'narrative' => $narrative,
            'teachers' => $teachersData,
            'currentSemester' => $selectedSemester,
            'activeTab' => 'departments',
        ]);
    }

    /**
     * Export department report in requested format.
     */
    public function export(Request $request, Department $department)
    {
        $format = $request->get('format', 'pdf');
        $selectedSemester = $this->sessionService->getSelectedSemester();

        return match ($format) {
            'pdf' => $this->exportService->exportDepartmentPDF($department, $selectedSemester),
            'csv' => $this->exportService->exportDepartmentCSV($department, $selectedSemester),
            default => back()->with('error', 'Invalid export format.'),
        };
    }
}
