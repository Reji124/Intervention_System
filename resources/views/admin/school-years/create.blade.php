@extends('layouts.admin')
@section('title','New School Year')
@section('page-title','New School Year')
@section('content')

<div class="form-card">
    <form method="POST" action="{{ route('admin.school-years.store') }}">
    @csrf

    <div class="field-row">
        <div class="field">
            <label>Year start <span class="req">*</span></label>
            <input type="number" name="year_start"
                value="{{ old('year_start', now()->year) }}"
                min="2000" max="2100" required>
            @error('year_start')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Year end <span class="req">*</span></label>
            <input type="number" name="year_end"
                value="{{ old('year_end', now()->year + 1) }}"
                min="2001" max="2101" required>
            @error('year_end')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="field">
        <label>Semesters to create</label>
        <div style="display:flex; gap:20px; margin-top:6px; flex-wrap:wrap;">
            <label class="check-label">
                <input type="checkbox" name="semesters[]" value="1st Semester"
                    {{ collect(old('semesters', ['1st Semester','2nd Semester']))->contains('1st Semester') ? 'checked' : '' }}>
                1st Semester
            </label>
            <label class="check-label">
                <input type="checkbox" name="semesters[]" value="2nd Semester"
                    {{ collect(old('semesters', ['1st Semester','2nd Semester']))->contains('2nd Semester') ? 'checked' : '' }}>
                2nd Semester
            </label>
            <label class="check-label">
                <input type="checkbox" name="semesters[]" value="Summer"
                    {{ collect(old('semesters', []))->contains('Summer') ? 'checked' : '' }}>
                Summer
            </label>
        </div>
        @error('semesters')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.school-years.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>

    </form>
</div>

@endsection