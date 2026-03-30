@extends('layouts.admin')
@section('title', 'Subjects')
@section('page-title', 'Subjects')
@section('content')

<style>
    .subjects-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }

    /* Department card */
    .dept-section {
        margin-bottom: 20px;
    }

    .dept-card {
        background: #fff;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }

    .dept-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        background: #f9fafb;
        border-bottom: 1px solid var(--border, #e5e7eb);
    }

    .dept-card-title {
        font-weight: 700;
        font-size: 0.92rem;
        color: #111827;
        flex: 1;
    }

    .dept-card-count {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        background: #e5e7eb;
        border-radius: 20px;
        padding: 2px 10px;
    }

    /* Year level group label */
    .year-group-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 20px;
        background: #f3f4f6;
        border-bottom: 1px solid var(--border, #e5e7eb);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: #9ca3af;
    }

    /* Table inside card */
    .dept-card table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .dept-card table thead tr th {
        background: #fff;
        padding: 10px 16px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--border, #e5e7eb);
        text-align: left;
    }

    .dept-card table tbody tr td {
        padding: 11px 16px;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .dept-card table tbody tr:last-child td {
        border-bottom: none;
    }

    .dept-card table tbody tr:hover td {
        background: #fafafa;
    }

    .td-main {
        font-weight: 600;
        color: #111827;
        display: block;
    }

    .td-sub {
        display: block;
        font-size: 11px;
        color: var(--text-soft, #9ca3af);
        margin-top: 2px;
    }

    /* Category badge */
    .badge-category {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        background: #eff6ff;
        color: #3b82f6;
        border: 1px solid #bfdbfe;
    }

    /* Action buttons */
    .action-group {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        cursor: pointer;
        border: 1.5px solid transparent;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, box-shadow 0.15s, transform 0.1s;
        white-space: nowrap;
    }

    .btn-action:active { transform: scale(0.96); }

    .btn-action-edit {
        background: #f5f5f5;
        border-color: #ddd;
        color: #444;
    }

    .btn-action-edit:hover {
        background: #ebebeb;
        border-color: #bbb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        color: #222;
        text-decoration: none;
    }

    .btn-action-delete {
        background: #fff5f5;
        border-color: #ffc9c9;
        color: #e03131;
    }

    .btn-action-delete:hover {
        background: #ffe3e3;
        border-color: #ff9a9a;
        box-shadow: 0 2px 8px rgba(224,49,49,0.13);
        color: #c92a2a;
    }

    .btn-action svg { flex-shrink: 0; }

    .delete-form { display: inline; margin: 0; padding: 0; }

    .empty-cell {
        text-align: center;
        padding: 28px;
        color: #9ca3af;
        font-size: 0.85rem;
        font-style: italic;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #9ca3af;
        background: #fff;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 12px;
        font-size: 0.9rem;
    }
</style>

<div class="subjects-toolbar">
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">+ New Subject</a>
</div>

@php
    {{-- Group subjects by department, then by year level (1→4) --}}
    $grouped = $subjects->groupBy(fn($s) => $s->department->department_name)
                        ->sortKeys();
@endphp

@forelse($grouped as $deptName => $deptSubjects)
<div class="dept-section">
    <div class="dept-card">

        {{-- Department header --}}
        <div class="dept-card-header">
            <span class="dept-card-title">{{ $deptName }}</span>
            <span class="dept-card-count">{{ $deptSubjects->count() }} {{ Str::plural('subject', $deptSubjects->count()) }}</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Course</th>
                    <th>Category</th>
                    <th>Year Level</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @php
                $byYear = $deptSubjects->groupBy('year_level')->sortKeys();
            @endphp

            @forelse($byYear as $yearLevel => $yearSubjects)
                {{-- Year level separator row --}}
                <tr>
                    <td colspan="5" style="padding:0;border-bottom:none;">
                        <div class="year-group-label">Year {{ $yearLevel }}</div>
                    </td>
                </tr>

                @foreach($yearSubjects->sortBy('subject_name') as $subject)
                <tr>
                    <td>
                        <span class="td-main">{{ $subject->subject_name }}</span>
                        <span class="td-sub">{{ $subject->subject_code }}</span>
                    </td>
                    <td>{{ $subject->course->course_name }}</td>
                    <td><span class="badge-category">{{ $subject->category }}</span></td>
                    <td>Year {{ $subject->year_level }}</td>
                    <td>
                        <div class="action-group">
                            {{-- Edit --}}
                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-action btn-action-edit">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </a>

                            {{-- Delete --}}
                            <form class="delete-form" method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                                  onsubmit="return confirm('Delete {{ $subject->subject_name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach

            @empty
                <tr><td colspan="5" class="empty-cell">No subjects in this department.</td></tr>
            @endforelse
            </tbody>
        </table>

    </div>
</div>
@empty
<div class="empty-state">No subjects yet.</div>
@endforelse

@endsection