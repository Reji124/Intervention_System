{{-- resources/views/admin/analytics/teachers/index.blade.php --}}
@extends('layouts.analytics')

@section('title', 'Teacher Reports')
@section('page-title', 'Teacher Reports')

@section('analytics-content')

<style>
    .pagination-wrapper {
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Constrain the SVG prev/next arrows */
    .pagination-wrapper svg {
        width: 14px;
        height: 14px;
    }

    .pagination-wrapper span,
    .pagination-wrapper a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid var(--border);
        color: var(--text-mid);
        text-decoration: none;
        transition: all 0.2s;
    }

    .pagination-wrapper a:hover {
        background: var(--gold-dim);
        border-color: var(--gold);
        color: var(--gold);
    }

    .pagination-wrapper span[aria-current="page"] {
        background: var(--navy);
        color: white;
        border-color: var(--navy);
        font-weight: 600;
    }

    .pagination-wrapper span[aria-disabled="true"] {
        opacity: 0.4;
        cursor: default;
    }

    .filter-bar {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1;
        min-width: 150px;
    }

    .filter-field label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-soft);
    }

    .filter-field input,
    .filter-field select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
        font-family: 'DM Sans', sans-serif;
        background: white;
        color: var(--text-dark);
    }

    .filter-btn {
        padding: 8px 16px;
        background: var(--navy);
        color: var(--white);
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn:hover {
        background: var(--navy-soft);
    }

    .teachers-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    .teachers-table thead th {
        background: #faf8f5;
        border-bottom: 1px solid var(--border);
        padding: 12px 16px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-soft);
    }

    .teachers-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3efe8;
        font-size: 13px;
        color: var(--text-mid);
        vertical-align: middle;
    }

    .teachers-table tbody tr:hover td {
        background: #faf8f5;
    }

    .risk-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
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

    .action-btn {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--gold);
        transition: all 0.2s;
        display: inline-block;
    }

    .action-btn:hover {
        background: var(--gold-dim);
        border-color: var(--gold);
    }
</style>

<div class="filter-bar">
    <form style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; flex: 1;">
        <div class="filter-field">
            <label>Search Teacher</label>
            <input type="text" name="search" placeholder="Name or code" value="{{ $filters['search'] ?? '' }}">
        </div>

        <div class="filter-field">
            <label>Department</label>
            <select name="department_id">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? null) == $dept->id ? 'selected' : '' }}>
                        {{ $dept->department_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="filter-btn">Filter</button>
    </form>
</div>

<table class="teachers-table">
    <thead>
        <tr>
            <th>Teacher Name</th>
            <th>Code</th>
            <th>Pass Rate</th>
            <th>Failed Students</th>
            <th>Total Students</th>
            <th>Risk Level</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($teachers as $teacher)
        <tr>
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
                <a href="{{ route('admin.analytics.teachers.show', $teacher['id']) }}" class="action-btn">
                    View Report →
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-soft);">
                No teachers found matching your filters.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($teachers->hasPages())
<div style="margin-top: 20px;">
    <div class="pagination-wrapper">
        {{ $teachers->links() }}
    </div>
</div>
@endif

@endsection
