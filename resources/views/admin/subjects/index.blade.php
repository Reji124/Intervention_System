@extends('layouts.admin')
@section('title', 'Subjects')
@section('page-title', 'Subjects')
@section('content')

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">+ New Subject</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Department</th>
                <th>Course</th>
                <th>Category</th>
                <th>Year Level</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subject)
        <tr>
            <td>
                <span class="td-main">{{ $subject->subject_name }}</span>
                <span class="td-sub">{{ $subject->subject_code }}</span>
            </td>
            <td>{{ $subject->department->department_name }}</td>
            <td>{{ $subject->course->course_name }}</td>
            <td>{{ $subject->category }}</td>
            <td>Year {{ $subject->year_level }}</td>
            <td class="td-actions">
                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-link">Edit</a>
                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                      style="display:inline" onsubmit="return confirm('Delete this subject?')">
                    @csrf @method('DELETE')
                    <button class="btn-link-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="empty-cell">No subjects yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<style>
.td-sub { display:block; font-size:11px; color:var(--text-soft); margin-top:2px; }
</style>
@endsection