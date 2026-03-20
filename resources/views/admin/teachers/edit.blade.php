@extends('layouts.admin')
@section('title','Edit Teacher')
@section('page-title','Edit Teacher')
@section('content')
 
<div class="form-card">
    <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}">
    @csrf @method('PUT')
 
    <div style="font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)">
        Teacher information
    </div>
 
    <div class="field-row">
        <div class="field">
            <label>Full name <span class="req">*</span></label>
            <input type="text" name="teacher_name"
                   value="{{ old('teacher_name', $teacher->teacher_name) }}" required>
            @error('teacher_name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Teacher code <span class="req">*</span></label>
            <input type="text" name="teacher_code"
                   value="{{ old('teacher_code', $teacher->teacher_code) }}"
                   pattern="[0-9]+" title="Numeric digits only" required>
            @error('teacher_code')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>
 
    <div class="field">
        <label>Email address</label>
        <input type="email" name="email"
               value="{{ old('email', $teacher->email) }}"
               placeholder="e.g. juan@school.com">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
    </div>
 
    <div class="form-actions">
        <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update teacher</button>
    </div>
    </form>
</div>
@endsection
