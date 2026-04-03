@extends('layouts.admin')
@section('title','Teachers')
@section('page-title','Teachers')
@section('content')

<style>
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

    .btn-action-manage {
        background: #f0f4ff;
        border-color: #c7d4f8;
        color: #3b5bdb;
    }
    .btn-action-manage:hover {
        background: #e0e9ff;
        border-color: #a5b9f5;
        box-shadow: 0 2px 8px rgba(59,91,219,0.13);
        color: #2f4bbf;
        text-decoration: none;
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

    .btn-action svg { flex-shrink: 0; }
    .delete-form { display: inline; margin: 0; padding: 0; }
</style>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">+ New teacher</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Teacher name</th>
                <th>Email</th>
                <th>Subjects</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($teachers as $teacher)
        <tr>
            <td>
                <span class="badge badge-mid" style="font-size:12px;padding:3px 10px;letter-spacing:.5px">
                    {{ $teacher->teacher_code ?? '—' }}
                </span>
            </td>
            <td><span class="td-main">{{ $teacher->teacher_name }}</span></td>
            <td style="font-size:12px;color:var(--text-soft)">{{ $teacher->email ?? '—' }}</td>
            <td>
                @forelse($teacher->teacherSubjects->take(3) as $ts)
                    <span class="badge badge-mid" style="margin-right:3px">
                        {{ $ts->subject->subject_code }}
                    </span>
                @empty
                    <span style="font-size:12px;color:var(--text-soft)">None yet</span>
                @endforelse
                @if($teacher->teacherSubjects->count() > 3)
                    <span style="font-size:11px;color:var(--text-soft)">
                        +{{ $teacher->teacherSubjects->count() - 3 }} more
                    </span>
                @endif
            </td>
            <td>
                <div class="action-group">
                    {{-- Manage --}}
                    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn-action btn-action-manage">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        Manage
                    </a>

                    {{-- Edit --}}
                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn-action btn-action-edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </a>

                    {{-- Delete --}}
                    <form class="delete-form" method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}"
                          onsubmit="return confirm('Delete {{ $teacher->teacher_name }}?')">
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
        @empty
        <tr><td colspan="5" class="empty-cell">No teachers yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection