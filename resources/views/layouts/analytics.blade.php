{{-- resources/views/layouts/analytics.blade.php --}}
@extends('layouts.admin')

@section('content')

<style>
    .analytics-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid var(--border);
        margin: -28px -32px 28px -32px;
        padding: 0 32px;
        background: var(--card-bg);
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

    .analytics-container {
        animation: slideUp 0.35s ease both;
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

        .analytics-tabs {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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

<div class="analytics-container">
    @yield('analytics-content')
</div>

@endsection
