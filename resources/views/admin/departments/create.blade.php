@extends('layouts.admin')
@section('title', 'New Department')
@section('page-title', 'New Department')
@section('content')

<div class="form-card">
    <form method="POST" action="{{ route('admin.departments.store') }}">
    @csrf

    <div class="field">
        <label>Department Name <span class="req">*</span></label>
        <input type="text" name="department_name" value="{{ old('department_name') }}"
               placeholder="e.g. College of Engineering and Technology" required>
        @error('department_name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label>Courses Offered</label>
        <div id="course-list">
            @if(old('courses'))
                @foreach(old('courses') as $i => $name)
                <div class="course-entry">
                    <input type="text" name="courses[]" value="{{ $name }}"
                           placeholder="e.g. Bachelor of Science in Information Technology">
                    <button type="button" class="btn-link-danger remove-course">Remove</button>
                </div>
                @endforeach
            @else
            <div class="course-entry">
                <input type="text" name="courses[]"
                       placeholder="e.g. Bachelor of Science in Information Technology">
                <button type="button" class="btn-link-danger remove-course">Remove</button>
            </div>
            @endif
        </div>
        <button type="button" id="add-course" class="btn btn-secondary" style="margin-top:8px">+ Add Course</button>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
    </form>
</div>

<style>
.course-entry { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
.course-entry input { flex:1; }
</style>

<script>
document.getElementById('add-course').addEventListener('click', function () {
    const div = document.createElement('div');
    div.className = 'course-entry';
    div.innerHTML = `<input type="text" name="courses[]" placeholder="e.g. Bachelor of Science in Information Technology">
                     <button type="button" class="btn-link-danger remove-course">Remove</button>`;
    document.getElementById('course-list').appendChild(div);
});

document.getElementById('course-list').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-course')) {
        e.target.closest('.course-entry').remove();
    }
});
</script>
@endsection