{{-- resources/views/admin/analytics/departments/index.blade.php --}}
@extends('layouts.analytics')

@section('title', 'Department Reports')
@section('page-title', 'Department Reports')

@section('analytics-content')

<style>
    .dept-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 16px;
    }

    .dept-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s;
        animation: slideUp 0.35s ease both;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .dept-card:hover {
        border-color: var(--gold);
        box-shadow: 0 4px 12px rgba(201,151,58,0.1);
    }

    .dept-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .dept-name {
        font-family: 'DM Serif Display', serif;
        font-size: 15px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .dept-stats {
        display: flex;
        gap: 12px;
        margin-top: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }

    .stat {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .stat-label {
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-soft);
    }

    .stat-value {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .dept-footer {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    .view-btn {
        flex: 1;
        padding: 8px;
        text-align: center;
        background: var(--navy);
        color: var(--white);
        text-decoration: none;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .view-btn:hover {
        background: var(--navy-soft);
    }

    .risk-badge.none {
        background: #eef0f3;
        color: var(--text-soft);
    }
</style>

<p style="font-size: 13px; color: var(--text-soft); margin-bottom: 20px;">
    Department-level performance analytics with teacher rankings and aggregate metrics.
</p>

<div class="dept-grid">
    @forelse($departments as $dept)
    @php($hasData = $dept['total_students'] > 0)
    <div class="dept-card">
        <div class="dept-header">
            <div class="dept-name">{{ $dept['name'] }}</div>
            <span class="risk-badge {{ $dept['risk_level'] }}" style="
                padding: 4px 8px;
                font-size: 9px;
            ">
                {{ strtoupper(str_replace(' ', '', $dept['risk_label'])) }}
            </span>
        </div>

        <div class="dept-stats">
            <div class="stat">
                <div class="stat-label">Pass Rate</div>
                <div class="stat-value">{{ $hasData ? $dept['pass_rate'] . '%' : 'No data' }}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Teachers</div>
                <div class="stat-value">{{ $dept['total_teachers'] }}</div>
            </div>
            <div class="stat">
                <div class="stat-label">Students</div>
                <div class="stat-value">{{ $dept['total_students'] }}</div>
            </div>
        </div>

        <div class="dept-footer">
            <a href="{{ route('admin.analytics.departments.show', $dept['id']) }}" class="view-btn">
                View Details →
            </a>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; color: var(--text-soft);">
        <p>No departments available.</p>
    </div>
    @endforelse
</div>

@endsection
