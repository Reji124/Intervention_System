@extends('layouts.admin')
@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')
@section('content')

<div class="form-card">
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
    @csrf @method('PUT')

    {{-- Department --}}
    <div class="field">
        <label>Department <span class="req">*</span></label>
        <select name="department_id" id="department_id" required>
            <option value="">— Select Department —</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}"
                    {{ old('department_id', $subject->department_id) == $dept->id ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select>
        @error('department_id')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    {{-- Course (cascading) --}}
    <div class="field">
        <label>Course <span class="req">*</span></label>
        <select name="course_id" id="course_id" required>
            {{-- Populated by JS --}}
            <option value="{{ $subject->course_id }}" selected>
                {{ $subject->course->course_name }}
            </option>
        </select>
        @error('course_id')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    {{-- Category --}}
    <div class="field">
        <label>Category <span class="req">*</span></label>
        <input type="text" name="category"
               value="{{ old('category', $subject->category) }}"
               placeholder="e.g. Professional, General Education" required>
        @error('category')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    {{-- Subject Code --}}
    <div class="field">
        <label>Subject Code <span class="req">*</span></label>
        <input type="text" name="subject_code"
               value="{{ old('subject_code', $subject->subject_code) }}"
               placeholder="e.g. IT101" required>
        @error('subject_code')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    {{-- Year Level --}}
    <div class="field">
        <label>Year Level <span class="req">*</span></label>
        <select name="year_level" required>
            <option value="">— Select —</option>
            @foreach([1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year'] as $val => $label)
                <option value="{{ $val }}"
                    {{ old('year_level', $subject->year_level) == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('year_level')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    {{-- Subject Name --}}
    <div class="field">
        <label>Subject Name <span class="req">*</span></label>
        <input type="text" name="subject_name"
               value="{{ old('subject_name', $subject->subject_name) }}"
               placeholder="e.g. Introduction to Programming" required>
        @error('subject_name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
    </form>
</div>

<script>
const deptSelect   = document.getElementById('department_id');
const courseSelect = document.getElementById('course_id');
const currentCourseId = "{{ old('course_id', $subject->course_id) }}";

async function loadCourses(deptId) {
    courseSelect.innerHTML = '<option value="">— Loading… —</option>';
    if (!deptId) {
        courseSelect.innerHTML = '<option value="">— Select Department first —</option>';
        return;
    }
    const res = await fetch(`/admin/departments/${deptId}/courses`);
    const courses = await res.json();
    courseSelect.innerHTML = '<option value="">— Select Course —</option>';
    courses.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.course_name;
        if (c.id == currentCourseId) opt.selected = true;
        courseSelect.appendChild(opt);
    });
}

deptSelect.addEventListener('change', () => loadCourses(deptSelect.value));

// Load courses for the current department on page load
loadCourses(deptSelect.value);
</script>
@endsection