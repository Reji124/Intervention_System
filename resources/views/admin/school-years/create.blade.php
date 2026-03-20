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
            <input type="number" name="year_start" value="{{ old('year_start', now()->year) }}" min="2000" max="2100" required>
            @error('year_start')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Year end <span class="req">*</span></label>
            <input type="number" name="year_end" value="{{ old('year_end', now()->year + 1) }}" min="2001" max="2101" required>
            @error('year_end')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="field">
        <label>Semesters to create</label>
        <div style="display:flex;gap:20px;margin-top:4px">
            <label class="check-label"><input type="checkbox" name="semesters[]" value="1st" checked> 1st Semester</label>
            <label class="check-label"><input type="checkbox" name="semesters[]" value="2nd" checked> 2nd Semester</label>
        </div>
    </div>
    <div class="form-actions">
        <a href="{{ route('admin.school-years.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
    </form>
</div>
@endsection