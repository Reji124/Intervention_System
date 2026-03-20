@extends('layouts.admin')
@section('title','Teachers')
@section('page-title','Teachers')
@section('content')
 
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
            <td class="td-actions">
                <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn-link">Manage</a>
                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn-link">Edit</a>
                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}"
                      style="display:inline"
                      onsubmit="return confirm('Delete {{ $teacher->teacher_name }}?')">
                    @csrf @method('DELETE')
                    <button class="btn-link-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="empty-cell">No teachers yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection