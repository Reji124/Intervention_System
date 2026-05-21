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
use App\Services\AnalyticsSessionService;
use App\Services\SubjectFactorAnalysisService;
use App\Services\YearOverYearComparisonService;
use App\Services\ExamTypeFactorAnalysisService;
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
        private ExportService $exportService,
        private AnalyticsSessionService $sessionService,
        private SubjectFactorAnalysisService $subjectFactorAnalysis,
        private YearOverYearComparisonService $yoyComparison,
        private ExamTypeFactorAnalysisService $examTypeFactorAnalysis
    ) {}

    /**
     * Display list of teachers with filterable analytics.
     */
    public function index(Request $request)
    {
        $selectedSemester = $this->sessionService->getSelectedSemester();

        $query = Teacher::with([
            'teacherSubjects' => function ($q) use ($selectedSemester) {
                $q->where('semester_id', $selectedSemester->id);
            },
            'teacherSubjects.exams.examResults',
            'teacherSubjects.subject.departments'
        ]);

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
        $teachers->getCollection()->transform(function ($teacher) use ($selectedSemester) {
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
                'failed_students' => $metrics['failed_students'],
                'total_students' => $metrics['total_students'],
                'risk_level' => $riskLevel['level'],
                'risk_label' => $riskLevel['label'],
            ];
        });

        $departments = Department::all();

        return view('admin.analytics.teachers.index', [
            'teachers' => $teachers,
            'departments' => $departments,
            'currentSemester' => $selectedSemester,
            'filters' => $request->all(),
            'activeTab' => 'teachers',
        ]);
    }

    /**
     * Display detailed analytics for a single teacher.
     */
    public function show(Teacher $teacher)
    {
        $selectedSemester = $this->sessionService->getSelectedSemester();

        $summary = $this->performanceCalculator->getTeacherPerformanceSummary($teacher, $selectedSemester);
        $overall = $this->performanceCalculator->getTeacherOverallMetrics($teacher, $selectedSemester);
        $subjectBreakdown = $this->performanceCalculator->getTeacherSubjectBreakdown($teacher, $selectedSemester);
        $narrative = $this->reportGenerator->generateTeacherNarrative($teacher, $selectedSemester);
        
        // YoY comparison data
        $yoyData = $this->yoyComparison->getTeacherComparison($teacher, $selectedSemester);
        
        // Overall factor analysis
        $overallFactorAnalysis = $this->examTypeFactorAnalysis->analyzeOverallPerformance($teacher, $selectedSemester);

        return view('admin.analytics.teachers.show', [
            'teacher' => $teacher,
            'summary' => $summary,
            'overall' => $overall,
            'subjectBreakdown' => $subjectBreakdown,
            'narrative' => $narrative,
            'yoyData' => $yoyData,
            'overallFactorAnalysis' => $overallFactorAnalysis,
            'currentSemester' => $selectedSemester,
            'activeTab' => 'teachers',
        ]);
    }

    /**
     * Get factor analysis for a specific subject.
     * Used via AJAX for modal display.
     */
    public function getSubjectFactorAnalysis(Request $request, Teacher $teacher)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $selectedSemester = $this->sessionService->getSelectedSemester();
        $subject = \App\Models\Subject::findOrFail($request->subject_id);

        $analysis = $this->subjectFactorAnalysis->analyzeSubjectPerformance(
            $teacher,
            $subject,
            $selectedSemester
        );

        return response()->json($analysis);
    }

    /**
     * Get factor analysis for a specific exam type.
     * Used via AJAX for modal display.
     */
    public function getExamTypeFactorAnalysis(Request $request, Teacher $teacher)
    {
        $request->validate([
            'exam_type' => 'required|string',
        ]);

        $selectedSemester = $this->sessionService->getSelectedSemester();
        $examType = $request->exam_type;

        $analysis = $this->examTypeFactorAnalysis->analyzeExamTypePerformance(
            $teacher,
            $examType,
            $selectedSemester
        );

        return response()->json($analysis);
    }

    /**
     * Export teacher report in requested format.
     */
    public function export(Request $request, Teacher $teacher)
    {
        $format = $request->get('format', 'pdf');
        $selectedSemester = $this->sessionService->getSelectedSemester();

        return match ($format) {
            'pdf' => $this->exportService->exportTeacherPDF($teacher, $selectedSemester),
            'csv' => $this->exportService->exportTeacherCSV($teacher, $selectedSemester),
            'print' => $this->exportService->getPrintableTeacherReport($teacher, $selectedSemester),
            default => back()->with('error', 'Invalid export format.'),
        };
    }
}
