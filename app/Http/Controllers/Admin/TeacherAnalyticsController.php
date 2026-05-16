<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\Semester;
use App\Services\PerformanceCalculator;
use App\Services\FactorAnalysisService;
use App\Services\ReportGenerator;
use App\Services\ExportService;
use Illuminate\Http\Request;

/**
 * TeacherAnalyticsController
 * 
 * Manages teacher-level analytics and reporting.
 * Displays teacher performance, subject breakdown, factor analysis, and exports.
 */
class TeacherAnalyticsController extends Controller
{
    public function __construct(
        private PerformanceCalculator $performanceCalculator,
        private FactorAnalysisService $factorAnalysisService,
        private ReportGenerator $reportGenerator,
        private ExportService $exportService
    ) {}

    /**
     * Display list of teachers with filterable analytics.
     */
    public function index(Request $request)
    {
        $query = Teacher::with('teacherSubjects.exams.examResults', 'teacherSubjects.subject.departments');

        // Filters
        if ($request->filled('department_id')) {
            $query->whereHas('teacherSubjects.subject.departments', function ($q) {
                $q->where('department_id', request('department_id'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('teacher_name', 'like', "%{$search}%")
                  ->orWhere('teacher_code', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(15);

        // Compute metrics for each teacher
        $teachers->getCollection()->transform(function ($teacher) {
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
        });

        $departments = Department::all();
        $currentSemester = Semester::where('is_active', true)->first();

        return view('admin.analytics.teachers.index', [
            'teachers' => $teachers,
            'departments' => $departments,
            'currentSemester' => $currentSemester,
            'filters' => $request->all(),
            'activeTab' => 'teachers',
        ]);
    }

    /**
     * Display detailed analytics for a single teacher.
     */
    public function show(Teacher $teacher)
    {
        $summary = $this->performanceCalculator->getTeacherPerformanceSummary($teacher);
        $overall = $this->performanceCalculator->getTeacherOverallMetrics($teacher);
        $subjectBreakdown = $this->performanceCalculator->getTeacherSubjectBreakdown($teacher);
        $analysis = $this->factorAnalysisService->getAnalysisSummary($teacher);
        $narrative = $this->reportGenerator->generateTeacherNarrative($teacher);

        return view('admin.analytics.teachers.show', [
            'teacher' => $teacher,
            'summary' => $summary,
            'overall' => $overall,
            'subjectBreakdown' => $subjectBreakdown,
            'analysis' => $analysis,
            'narrative' => $narrative,
            'activeTab' => 'teachers',
        ]);
    }

    /**
     * Export teacher report in requested format.
     */
    public function export(Request $request, Teacher $teacher)
    {
        $format = $request->get('format', 'pdf');

        return match ($format) {
            'pdf' => $this->exportService->exportTeacherPDF($teacher),
            'csv' => $this->exportService->exportTeacherCSV($teacher),
            'print' => $this->exportService->getPrintableTeacherReport($teacher),
            default => back()->with('error', 'Invalid export format.'),
        };
    }
}
