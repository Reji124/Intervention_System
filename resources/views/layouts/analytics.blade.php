{{-- resources/views/layouts/analytics.blade.php --}}
@extends('layouts.admin')

@section('content')

<style>
    .analytics-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: -28px -32px 0 -32px;
        padding: 0 32px;
        background: var(--card-bg);
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 16px;
        min-height: 64px;
    }

    .analytics-tabs {
        display: flex;
        gap: 0;
        border-bottom: none;
        margin: 0;
        padding: 0;
        background: transparent;
        flex: 1;
        flex-wrap: wrap;
    }

    .analytics-tab {
        padding: 16px 20px;
        border-bottom: 3px solid transparent;
        color: var(--text-soft);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .analytics-tab:hover {
        color: var(--gold);
        background: var(--gold-dim);
    }

    .analytics-tab.active {
        color: var(--gold);
        border-bottom-color: var(--gold);
        background: var(--gold-dim);
    }

    .analytics-sy-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-right: 16px;
    }

    .analytics-sy-label {
        font-size: 12px;
        color: var(--text-soft);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .analytics-sy-dropdown {
        font-family: 'DM Sans', sans-serif;
        padding: 8px 12px;
        border: 1.5px solid var(--border);
        border-radius: 6px;
        background: var(--card-bg);
        color: var(--text-dark);
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .analytics-sy-dropdown:hover {
        border-color: var(--gold);
        background: #faf8f5;
    }

    .analytics-sy-dropdown:focus {
        outline: none;
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(183, 98, 26, 0.1);
    }

    .analytics-container {
        animation: slideUp 0.35s ease both;
        margin-top: 28px;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Grid layouts for dashboards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    @media (max-width: 768px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }

        .analytics-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .analytics-sy-filter {
            width: 100%;
            padding-right: 0;
        }

        .analytics-sy-dropdown {
            flex: 1;
        }
    }

    .section-title {
        font-family: 'DM Serif Display', serif;
        font-size: 16px;
        color: var(--text-dark);
        margin-bottom: 16px;
        margin-top: 28px;
        font-weight: 500;
    }

    .section-title:first-child {
        margin-top: 0;
    }
</style>

<div class="analytics-header">
    <div class="analytics-tabs">
        <a href="{{ route('admin.analytics.dashboard') }}" 
           class="analytics-tab {{ $activeTab === 'dashboard' ? 'active' : '' }}">
            <svg style="width: 14px; height: 14px; margin-right: 6px; display: inline-block; vertical-align: -2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>
        <a href="{{ route('admin.analytics.teachers.index') }}" 
           class="analytics-tab {{ $activeTab === 'teachers' ? 'active' : '' }}">
            <svg style="width: 14px; height: 14px; margin-right: 6px; display: inline-block; vertical-align: -2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Teacher Reports
        </a>
        <a href="{{ route('admin.analytics.departments.index') }}" 
           class="analytics-tab {{ $activeTab === 'departments' ? 'active' : '' }}">
            <svg style="width: 14px; height: 14px; margin-right: 6px; display: inline-block; vertical-align: -2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Department Reports
        </a>
    </div>

    <div class="analytics-sy-filter">
        <label class="analytics-sy-label">Filter:</label>
        <form id="analytics-sy-form" method="POST" action="{{ route('admin.analytics.set-semester') }}" style="display: inline;">
            @csrf
            <select name="semester_id" class="analytics-sy-dropdown" onchange="document.getElementById('analytics-sy-form').submit();">
                @php
                    $selectedSemester = $currentSemester ?? null;
                    $allSchoolYears = \App\Models\SchoolYear::with('semesters')->orderByDesc('year_start')->get();
                @endphp

                @foreach($allSchoolYears as $schoolYear)
                    @foreach($schoolYear->semesters as $semester)
                        <option value="{{ $semester->id }}" 
                            {{ $selectedSemester && $selectedSemester->id === $semester->id ? 'selected' : '' }}>
                            SY {{ $schoolYear->year_start }}–{{ $schoolYear->year_end }} • {{ $semester->semester_name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="analytics-container">
    @yield('analytics-content')
</div>

@endsection
