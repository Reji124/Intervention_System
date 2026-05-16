{{-- resources/views/admin/analytics/departments/show.blade.php --}}
@extends('layouts.analytics')

@section('title', $department->department_name . ' - Department Report')
@section('page-title', $department->department_name)

@section('analytics-content')

<style>
    .report-section {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        animation: slideUp 0.35s ease both;
    }

    .section-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        background: #faf8f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-header h3 {
        margin: 0;
        font-family: 'DM Serif Display', serif;
        font-size: 14px;
        color: var(--text-dark);
    }

    .section-body {
        padding: 20px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-item {
        background: #faf8f5;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }

    .summary-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-soft);
        margin-bottom: 6px;
    }

    .summary-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .teachers-table {
        width: 100%;
        border-collapse: collapse;
    }

    .teachers-table thead th {
        background: #faf8f5;
        border-bottom: 1px solid var(--border);
        padding: 10px 12px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-soft);
    }

    .teachers-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3efe8;
        font-size: 12px;
    }

    .teachers-table tbody tr:hover {
        background: #faf8f5;
    }

    .narrative-box {
        background: #f9f7f2;
        border-left: 3px solid var(--gold);
        padding: 14px;
        border-radius: 4px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-mid);
    }

    .risk-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 16px;
        font-size: 10px;
        font-weight: 600;
    }

    .risk-badge.low {
        background: var(--green-bg);
        color: var(--green);
    }

    .risk-badge.moderate {
        background: var(--amber-bg);
        color: var(--amber);
    }

    .risk-badge.high {
        background: var(--red-bg);
        color: var(--red);
    }

    .export-btn {
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--navy);
        color: var(--white);
    }

    .export-btn:hover {
        background: var(--navy-soft);
    }
</style>

{{-- Department Summary --}}
<div class="report-section">
    <div class="section-header">
        <h3>Department Summary</h3>
        <div style="display: flex; gap: 10px;">
            <form method="POST" action="{{ route('admin.analytics.departments.export', $department) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="export-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Export PDF
                </button>
            </form>
            <form method="POST" action="{{ route('admin.analytics.departments.export', $department) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="format" value="csv">
                <button type="submit" class="export-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Excel
                </button>
            </form>
        </div>
    </div>
    <div class="section-body">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Pass Rate</div>
                <div class="summary-value">{{ $metrics['pass_rate'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Teachers</div>
                <div class="summary-value">{{ $metrics['total_teachers'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Students</div>
                <div class="summary-value">{{ $metrics['total_students'] }}</div>
            </div>
        </div>

        <div class="narrative-box">
            {{ $narrative }}
        </div>
    </div>
</div>

{{-- Teacher Rankings --}}
<div class="report-section">
    <div class="section-header">
        <h3>Teacher Performance Rankings</h3>
    </div>
    <div class="section-body">
        <table class="teachers-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Teacher Name</th>
                    <th>Code</th>
                    <th>Pass Rate</th>
                    <th>Failed Students</th>
                    <th>Total Students</th>
                    <th>Risk Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $rank => $teacher)
                <tr>
                    <td style="font-weight: 600;">{{ $rank + 1 }}</td>
                    <td style="font-weight: 500;">{{ $teacher['name'] }}</td>
                    <td>{{ $teacher['code'] ?? 'N/A' }}</td>
                    <td style="font-weight: 600; color: var(--text-dark);">{{ $teacher['pass_rate'] }}%</td>
                    <td>{{ $teacher['failed_students'] }}</td>
                    <td>{{ $teacher['total_students'] }}</td>
                    <td>
                        <span class="risk-badge {{ $teacher['risk_level'] }}">
                            {{ $teacher['risk_label'] }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.analytics.teachers.show', $teacher['id']) }}" style="
                            color: var(--gold);
                            text-decoration: none;
                            font-size: 11px;
                            font-weight: 600;
                        ">
                            View →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: var(--text-soft);">
                        No teachers found for this department.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
