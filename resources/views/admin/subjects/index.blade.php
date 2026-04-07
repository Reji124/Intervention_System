@extends('layouts.admin')
@section('title', 'Subjects')
@section('page-title', 'Subjects')
@section('content')

<style>
    .subjects-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .subjects-meta {
        font-size: 0.82rem;
        color: #6b7280;
    }

    /* ── Tab Shell ── */
    .tab-shell {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }

    .tab-bar {
        display: flex;
        gap: 0;
        padding: 0 4px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .tab-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 22px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #6b7280;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        cursor: pointer;
        transition: color 0.15s;
        white-space: nowrap;
        text-decoration: none;
    }

    .tab-btn:hover { color: #374151; }

    .tab-btn.active {
        color: #1d4ed8;
        background: #fff;
        border-bottom: 2px solid #2563eb;
    }

    .tab-cnt {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        background: #e5e7eb;
        color: #6b7280;
        border: 1px solid transparent;
    }

    .tab-btn.active .tab-cnt {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .tab-body { padding: 20px; }

    /* ── Year Panels ── */
    .year-panel { display: none; }
    .year-panel.active { display: block; }

    /* ── Subject Cards ── */
    .subj-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .subj-card:last-child { margin-bottom: 0; }

    .subj-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .subject-code-pill {
        flex-shrink: 0;
        font-size: 0.72rem;
        font-weight: 700;
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 3px 10px;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }

    .subj-name {
        flex: 1;
        font-weight: 700;
        font-size: 0.9rem;
        color: #111827;
    }

    .badge-category {
        flex-shrink: 0;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        color: #6b7280;
        background: #fff;
    }

    .action-group {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-shrink: 0;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 13px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid transparent;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, transform 0.1s;
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
        color: #222;
        text-decoration: none;
    }

    .btn-action-delete {
        background: #fff5f5;
        border-color: #fecaca;
        color: #dc2626;
    }

    .btn-action-delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }

    .btn-action svg { flex-shrink: 0; }
    .delete-form { display: inline; margin: 0; padding: 0; }

    .subj-card table {
        width: 100%;
        border-collapse: collapse;
    }

    .subj-card table thead th {
        padding: 9px 16px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
        background: #fff;
    }

    .subj-card table tbody td {
        padding: 10px 16px;
        font-size: 0.84rem;
        color: #374151;
        border-bottom: 1px solid #f9fafb;
        vertical-align: middle;
    }

    .subj-card table tbody tr:last-child td { border-bottom: none; }
    .subj-card table tbody tr:hover td { background: #fafafa; }

    .td-dept { color: #9ca3af; font-size: 0.8rem; }

    .empty-row {
        text-align: center;
        padding: 20px;
        color: #9ca3af;
        font-size: 0.84rem;
        font-style: italic;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #9ca3af;
        font-size: 0.9rem;
    }
</style>

@php
    $grouped = $subjects->groupBy('year_level')->sortKeys();
    $yearLabels = [1=>'Year 1', 2=>'Year 2', 3=>'Year 3', 4=>'Year 4', 5=>'Year 5'];
    $firstYear = $grouped->keys()->first();
@endphp

<div class="subjects-toolbar">
    <span class="subjects-meta">{{ $subjects->count() }} subject{{ $subjects->count() !== 1 ? 's' : '' }} across {{ $grouped->count() }} year level{{ $grouped->count() !== 1 ? 's' : '' }}</span>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">+ New Subject</a>
</div>

@if($subjects->isEmpty())
    <div class="tab-shell">
        <div class="tab-body">
            <div class="empty-state">
                No subjects yet. <a href="{{ route('admin.subjects.create') }}">Create one.</a>
            </div>
        </div>
    </div>
@else

<div class="tab-shell">

    {{-- Tab Bar --}}
    <div class="tab-bar">
        @foreach($grouped as $year => $yearSubjects)
            <button class="tab-btn {{ $year === $firstYear ? 'active' : '' }}"
                    onclick="switchYear({{ $year }}, this)">
                {{ $yearLabels[$year] ?? 'Year '.$year }}
                <span class="tab-cnt">{{ $yearSubjects->count() }}</span>
            </button>
        @endforeach
    </div>

    {{-- Tab Content --}}
    <div class="tab-body">
        @foreach($grouped as $year => $yearSubjects)
            <div class="year-panel {{ $year === $firstYear ? 'active' : '' }}"
                 id="year-panel-{{ $year }}">

                @foreach($yearSubjects as $subject)
                    <div class="subj-card">
                        <div class="subj-header">
                            <span class="subject-code-pill">{{ $subject->subject_code }}</span>
                            <span class="subj-name">{{ $subject->subject_name }}</span>
                            <span class="badge-category">{{ $subject->category }}</span>
                            <div class="action-group">
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-action btn-action-edit">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </a>
                                <form class="delete-form" method="POST"
                                      action="{{ route('admin.subjects.destroy', $subject) }}"
                                      onsubmit="return confirm('Delete {{ $subject->subject_name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($subject->courses->isNotEmpty())
                            <table>
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Course</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subject->courses as $course)
                                        <tr>
                                            <td class="td-dept">{{ $course->department->department_name ?? '—' }}</td>
                                            <td>{{ $course->course_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty-row">No courses assigned to this subject.</div>
                        @endif
                    </div>
                @endforeach

            </div>
        @endforeach
    </div>

</div>
@endif

<script>
function switchYear(year, el) {
    document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.year-panel').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('year-panel-' + year).classList.add('active');
}
</script>

@endsection