@extends('layouts.admin')
@section('title', 'Departments')
@section('page-title', 'Departments')
@section('content')

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">+ New Department</a>
</div>

<div class="card">
    @forelse($departments as $dept)
    <div class="dept-row">
        <div class="dept-header">
            <div>
                <span class="dept-name">{{ $dept->department_name }}</span>
                <span class="dept-meta">{{ $dept->courses->count() }} {{ Str::plural('course', $dept->courses->count()) }} &middot; {{ $dept->subjects_count }} {{ Str::plural('subject', $dept->subjects_count) }}</span>
            </div>
            <div class="td-actions">
                <a href="{{ route('admin.departments.edit', $dept) }}" class="btn-link">Edit</a>
                <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}"
                      style="display:inline" onsubmit="return confirm('Delete this department and all its courses?')">
                    @csrf @method('DELETE')
                    <button class="btn-link-danger">Delete</button>
                </form>
            </div>
        </div>

        @if($dept->courses->isNotEmpty())
        <ul class="course-list">
            @foreach($dept->courses as $course)
            <li>{{ $course->course_name }}</li>
            @endforeach
        </ul>
        @else
        <p class="no-courses">No courses added yet.</p>
        @endif
    </div>
    @empty
    <p class="empty-cell">No departments yet.</p>
    @endforelse
</div>

<style>
.dept-row { padding: 16px 20px; border-bottom: 1px solid var(--border, #e5e7eb); }
.dept-row:last-child { border-bottom: none; }
.dept-header { display:flex; justify-content:space-between; align-items:flex-start; }
.dept-name { font-weight: 600; font-size: 0.95rem; display:block; }
.dept-meta { font-size: 0.8rem; color: #6b7280; margin-top: 2px; display:block; }
.course-list { margin: 10px 0 0 0; padding-left: 20px; list-style: disc; }
.course-list li { font-size: 0.875rem; color: #374151; padding: 2px 0; }
.no-courses { font-size: 0.85rem; color: #9ca3af; margin-top: 8px; }
</style>
@endsection