@extends('layouts.admin')
@section('title', 'Edit Department')
@section('page-title', 'Edit Department')
@section('content')

<div class="form-card">
    <form method="POST" action="{{ route('admin.departments.update', $department) }}">
    @csrf @method('PUT')

    <div class="field">
        <label>Department Name <span class="req">*</span></label>
        <input type="text" name="department_name"
               value="{{ old('department_name', $department->department_name) }}" required>
        @error('department_name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label>Courses Offered</label>
        <div id="course-list">
            @foreach($department->courses as $course)
            <div class="course-entry">
                <input type="hidden" name="courses[{{ $loop->index }}][id]" value="{{ $course->id }}">
                <input type="text"   name="courses[{{ $loop->index }}][name]"
                       value="{{ old('courses.'.$loop->index.'.name', $course->course_name) }}"
                       placeholder="Course name">
                <button type="button" class="btn-link-danger remove-course">Remove</button>
            </div>
            @endforeach
        </div>
        <button type="button" id="add-course" class="btn btn-secondary" style="margin-top:8px">+ Add Course</button>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
    </form>
</div>

<style>
.course-entry { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
.course-entry input[type=text] { flex:1; }
</style>

<script>
let newIndex = {{ $department->courses->count() }};

document.getElementById('add-course').addEventListener('click', function () {
    const div = document.createElement('div');
    div.className = 'course-entry';
    div.innerHTML = `<input type="text" name="courses[${newIndex}][name]" placeholder="Course name">
                     <button type="button" class="btn-link-danger remove-course">Remove</button>`;
    document.getElementById('course-list').appendChild(div);
    newIndex++;
});

document.getElementById('course-list').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-course')) {
        e.target.closest('.course-entry').remove();
    }
});
</script>
@endsection