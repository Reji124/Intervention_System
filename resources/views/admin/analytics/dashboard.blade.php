{{-- resources/views/admin/analytics/dashboard.blade.php --}}
@extends('layouts.analytics')

@section('title', 'Academic Analytics Dashboard')
@section('page-title', 'Academic Analytics Dashboard')

@section('analytics-content')

<style>
    .dashboard-header {
        margin-bottom: 28px;
    }

    .dashboard-intro {
        font-size: 13px;
        color: var(--text-soft);
        max-width: 600px;
    }
</style>

<div class="dashboard-header">
    <p class="dashboard-intro">
        Executive overview of institutional academic performance. Monitor key performance indicators, identify at-risk areas, and make data-driven decisions.
    </p>
</div>

<h2 class="section-title">Key Performance Indicators</h2>

<div class="kpi-grid">
    {{-- Overall Pass Rate --}}
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Overall School Pass Rate',
        'value' => $kpis['overall_pass_rate'] . '%',
        'unit' => 'All exams, all students',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
    ])

    {{-- Failed Students --}}
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Total Failed Students',
        'value' => $kpis['total_failed_students'],
        'unit' => 'Requires intervention',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
    ])

    {{-- Highest Performing Department --}}
    @php
        $bestDept = $kpis['highest_performing_department'];
    @endphp
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Highest Performing Department',
        'value' => $bestDept ? $bestDept['pass_rate'] . '%' : 'N/A',
        'unit' => $bestDept ? $bestDept['name'] : 'No data',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    ])

    {{-- Lowest Performing Department --}}
    @php
        $worstDept = $kpis['lowest_performing_department'];
    @endphp
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Lowest Performing Department',
        'value' => $worstDept ? $worstDept['pass_rate'] . '%' : 'N/A',
        'unit' => $worstDept ? $worstDept['name'] : 'No data',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
    ])

    {{-- Highest Risk Subject --}}
    @php
        $riskSubject = $kpis['highest_risk_subject'];
    @endphp
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Highest Risk Subject',
        'value' => $riskSubject ? $riskSubject['pass_rate'] . '%' : 'N/A',
        'unit' => $riskSubject ? $riskSubject['name'] : 'No data',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
    ])

    {{-- Highest Risk Teacher --}}
    @php
        $riskTeacher = $kpis['highest_risk_teacher'];
    @endphp
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Highest Risk Teacher',
        'value' => $riskTeacher ? $riskTeacher['pass_rate'] . '%' : 'N/A',
        'unit' => $riskTeacher ? $riskTeacher['name'] : 'No data',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
    ])

    {{-- Most Difficult Exam Type --}}
    @php
        $difficultExam = $kpis['most_difficult_exam_type'];
    @endphp
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Most Difficult Exam Type',
        'value' => $difficultExam ? $difficultExam['pass_rate'] . '%' : 'N/A',
        'unit' => $difficultExam ? $difficultExam['exam_type'] : 'No data',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-2.1-2.54-4.12 5.41h12.9L13.96 12.29z"/></svg>',
    ])

    {{-- Intervention Success Rate --}}
    @include('admin.analytics.components._kpi-card', [
        'label' => 'Intervention Success Rate',
        'value' => $kpis['intervention_success_rate'] . '%',
        'unit' => 'Prelim to Final improvement',
        'icon' => '<svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.8429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.6889879 L4.13399899,1.16865249 C3.34915502,0.9115551 2.40734225,1.0216337 1.77946707,1.4929259 C0.994623095,2.1272231 0.837654326,3.21657426 1.15159189,3.95678399 L3.03521743,10.3977773 C3.03521743,10.5548747 3.19218622,10.7119721 3.50612381,10.7119721 L16.6915026,11.4974590 C16.6915026,11.4974590 17.1624089,11.4974590 17.1624089,12.0685671 C17.1624089,12.4744748 16.6915026,12.4744748 16.6915026,12.4744748 Z"/></svg>',
    ])
</div>

<h2 class="section-title">Quick Actions</h2>

<div style="
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
">
    <a href="{{ route('admin.analytics.teachers.index') }}" style="
        padding: 16px 20px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
        font-size: 13px;
        font-weight: 500;
    " onmouseover="this.style.borderColor = 'var(--gold)'; this.style.background = 'var(--gold-dim)'" onmouseout="this.style.borderColor = 'var(--border)'; this.style.background = 'var(--card-bg)'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
        View Teacher Reports
    </a>

    <a href="{{ route('admin.analytics.departments.index') }}" style="
        padding: 16px 20px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
        font-size: 13px;
        font-weight: 500;
    " onmouseover="this.style.borderColor = 'var(--gold)'; this.style.background = 'var(--gold-dim)'" onmouseout="this.style.borderColor = 'var(--border)'; this.style.background = 'var(--card-bg)'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        View Department Reports
    </a>
</div>

@endsection
