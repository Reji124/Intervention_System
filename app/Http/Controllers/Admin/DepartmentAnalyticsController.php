<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Semester;
use App\Services\PerformanceCalculator;
use App\Services\ReportGenerator;
use App\Services\ExportService;
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
        private ExportService $exportService
    ) {}

    /**
     * Display list of departments with aggregated analytics.
     */
    public function index(Request $request)
    {
        $departments = Department::all();

        // Compute metrics for each department
        $departmentData = $departments->map(function ($dept) {
            $metrics = $this->performanceCalculator->getDepartmentMetrics($dept);
            $riskLevel = \App\Services\AnalyticsService::getRiskLevel($metrics['pass_rate']);

            return [
                'id' => $dept->id,
                'name' => $dept->department_name,
                'pass_rate' => $metrics['pass_rate'],
                'total_teachers' => $metrics['total_teachers'],
                'total_students' => $metrics['total_students'],
                'risk_level' => $riskLevel['level'],
                'risk_label' => $riskLevel['label'],
            ];
        })->sortByDesc('pass_rate');

        $currentSemester = Semester::where('is_active', true)->first();

        return view('admin.analytics.departments.index', [
            'departments' => $departmentData,
            'currentSemester' => $currentSemester,
            'activeTab' => 'departments',
        ]);
    }

    /**
     * Display detailed analytics for a single department.
     */
    public function show(Department $department)
    {
        $metrics = $this->performanceCalculator->getDepartmentMetrics($department);
        $narrative = $this->reportGenerator->generateDepartmentNarrative($department);

        // Get all teachers in department
        $courses = $department->courses()
            ->with('subjects.teacherSubjects.teacher.exams.examResults')
            ->get();

        $teachers = $courses->flatMap(function ($course) {
            return $course->subjects->flatMap(function ($subject) {
                return $subject->teacherSubjects->pluck('teacher');
            });
        })->unique('id');

        // Compute teacher metrics
        $teacherData = $teachers->map(function ($teacher) {
            $metrics = $this->performanceCalculator->getTeacherOverallMetrics($teacher);
            $riskLevel = \App\Services\AnalyticsService::getRiskLevel($metrics['pass_rate']);

            return [
                'id' => $teacher->id,
                'name' => $teacher->teacher_name,
                'code' => $teacher->teacher_code,
                'pass_rate' => $metrics['pass_rate'],
                'failed_students' => $metrics['failed_students'],
                'total_students' => $metrics['total_students'],
                'risk_level' => $riskLevel['level'],
                'risk_label' => $riskLevel['label'],
            ];
        })->sortByDesc('pass_rate');

        return view('admin.analytics.departments.show', [
            'department' => $department,
            'metrics' => $metrics,
            'narrative' => $narrative,
            'teachers' => $teacherData,
            'activeTab' => 'departments',
        ]);
    }

    /**
     * Export department report in requested format.
     */
    public function export(Request $request, Department $department)
    {
        $format = $request->get('format', 'pdf');

        return match ($format) {
            'pdf' => $this->exportService->exportDepartmentPDF($department),
            'csv' => $this->exportService->exportDepartmentCSV($department),
            default => back()->with('error', 'Invalid export format.'),
        };
    }
}
