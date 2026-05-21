<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\AnalyticsSessionService;
use Illuminate\Http\Request;

/**
 * AnalyticsController
 * 
 * Manages the Academic Analytics and Intervention Intelligence Module.
 * Displays executive dashboard with institutional KPIs.
 */
class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private AnalyticsSessionService $sessionService
    ) {}

    /**
     * Display the analytics dashboard with institutional KPIs.
     */
    public function dashboard()
    {
        $kpis = [
            'total_exam_results' => $this->analyticsService->getTotalExamResults(),
            'overall_pass_rate' => $this->analyticsService->getOverallPassRate(),
            'total_failed_students' => $this->analyticsService->getTotalFailedStudents(),
            'highest_performing_department' => $this->analyticsService->getHighestPerformingDepartment(),
            'lowest_performing_department' => $this->analyticsService->getLowestPerformingDepartment(),
            'highest_risk_subject' => $this->analyticsService->getHighestRiskSubject(),
            'highest_risk_teacher' => $this->analyticsService->getHighestRiskTeacher(),
            'most_difficult_exam_type' => $this->analyticsService->getMostDifficultExamType(),
            'intervention_success_rate' => $this->analyticsService->getInterventionSuccessRate(),
        ];

        return view('admin.analytics.dashboard', [
            'kpis' => $kpis,
            'activeTab' => 'dashboard',
            'currentSemester' => $this->sessionService->getSelectedSemester(),
        ]);
    }

    /**
     * Set the selected semester in session.
     */
    public function setSemester(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $this->sessionService->setSelectedSemester($request->integer('semester_id'));

        return back();
    }

    /**
     * Clear analytics caches (admin action).
     */
    public function clearCache()
    {
        $this->analyticsService->clearCache();

        return back()->with('success', 'Analytics cache cleared successfully.');
    }
}
