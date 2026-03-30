@extends('layouts.admin')
@section('title', 'Departments')
@section('page-title', 'Departments')
@section('content')

<style>
    .dept-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }

    .dept-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .dept-card {
        background: #fff;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        transition: box-shadow 0.15s;
    }

    .dept-card:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    .dept-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .dept-info {
        flex: 1;
        min-width: 0;
    }

    .dept-name {
        font-weight: 700;
        font-size: 0.97rem;
        color: #111827;
        display: block;
    }

    .dept-meta {
        font-size: 0.78rem;
        color: #6b7280;
        margin-top: 3px;
        display: block;
    }

    /* Action buttons — same system as school years */
    .action-group {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
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

    .btn-action:active {
        transform: scale(0.96);
    }

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

    .btn-action svg {
        flex-shrink: 0;
    }

    .delete-form {
        display: inline;
        margin: 0;
        padding: 0;
    }

    /* Divider between header and courses */
    .dept-divider {
        border: none;
        border-top: 1px solid var(--border, #e5e7eb);
        margin: 14px 0 12px;
    }

    .course-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .course-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 3px 10px;
        font-size: 0.8rem;
        color: #374151;
        font-weight: 500;
    }

    .course-chip svg {
        color: #9ca3af;
        flex-shrink: 0;
    }

    .no-courses {
        font-size: 0.82rem;
        color: #9ca3af;
        margin: 0;
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

<div class="dept-toolbar">
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">+ New Department</a>
</div>

<div class="dept-grid">
    @forelse($departments as $dept)
    <div class="dept-card">

        {{-- Header: name + actions --}}
        <div class="dept-header">
            <div class="dept-info">
                <span class="dept-name">{{ $dept->department_name }}</span>
                <span class="dept-meta">
                    {{ $dept->courses->count() }} {{ Str::plural('course', $dept->courses->count()) }}
                    &middot;
                    {{ $dept->subjects_count }} {{ Str::plural('subject', $dept->subjects_count) }}
                </span>
            </div>

            <div class="action-group">
                {{-- Edit --}}
                <a href="{{ route('admin.departments.edit', $dept) }}" class="btn-action btn-action-edit">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit
                </a>

                {{-- Delete --}}
                <form class="delete-form" method="POST" action="{{ route('admin.departments.destroy', $dept) }}"
                      onsubmit="return confirm('Delete {{ $dept->department_name }} and all its courses?')">
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
        </div>

        {{-- Courses --}}
        @if($dept->courses->isNotEmpty())
            <hr class="dept-divider">
            <ul class="course-list">
                @foreach($dept->courses as $course)
                <li>
                    <span class="course-chip">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                        {{ $course->course_name }}
                    </span>
                </li>
                @endforeach
            </ul>
        @else
            <hr class="dept-divider">
            <p class="no-courses">No courses added yet.</p>
        @endif

    </div>
    @empty
    <div class="empty-state">No departments yet.</div>
    @endforelse
</div>

@endsection