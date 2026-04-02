@extends('layouts.admin')
@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')
@section('content')

<div class="form-card">
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
    @csrf @method('PUT')

    <div class="field">
        <label>Subject Code <span class="req">*</span></label>
        <input type="text" name="subject_code"
               value="{{ old('subject_code', $subject->subject_code) }}"
               placeholder="e.g. IT101" required>
        @error('subject_code')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label>Subject Name <span class="req">*</span></label>
        <input type="text" name="subject_name"
               value="{{ old('subject_name', $subject->subject_name) }}"
               placeholder="e.g. Introduction to Programming" required>
        @error('subject_name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label>Category <span class="req">*</span></label>
        <input type="text" name="category"
               value="{{ old('category', $subject->category) }}"
               placeholder="e.g. Professional, General Education" required>
        @error('category')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label>Year Level <span class="req">*</span></label>
        <select name="year_level" required>
            <option value="">— Select —</option>
            @foreach([1=>'1st Year',2=>'2nd Year',3=>'3rd Year',4=>'4th Year',5=>'5th Year'] as $v => $l)
                <option value="{{ $v }}"
                    {{ old('year_level', $subject->year_level) == $v ? 'selected' : '' }}>
                    {{ $l }}
                </option>
            @endforeach
        </select>
        @error('year_level')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    {{-- Assignments --}}
    <div class="field">
        <label>Department &amp; Course Assignments <span class="req">*</span></label>
        @error('assignments')<p class="field-error">{{ $message }}</p>@enderror

        <div id="assignments-list">
        @php
            // On validation error, restore from old(); otherwise use current pivot data
            $existingAssignments = old('assignments')
                ? collect(old('assignments'))->map(fn($a) => (object)[
                    'department_id' => $a['department_id'],
                    'course_id'     => $a['course_id'],
                  ])
                : $subject->courses->map(fn($c) => (object)[
                    'department_id' => $c->pivot->department_id,
                    'course_id'     => $c->id,
                  ]);
        @endphp

        @foreach($existingAssignments as $i => $assignment)
            <div class="assignment-row" data-index="{{ $i }}">
                <select name="assignments[{{ $i }}][department_id]"
                        class="dept-select" required>
                    <option value="">— Department —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{ $assignment->department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>

                <select name="assignments[{{ $i }}][course_id]"
                        class="course-select" required>
                    {{-- Courses for the selected dept, with current course selected --}}
                    @php
                        $deptForRow = $departments->firstWhere('id', $assignment->department_id);
                    @endphp
                    @if($deptForRow)
                        <option value="">— Select Course —</option>
                        @foreach($deptForRow->courses as $course)
                            <option value="{{ $course->id }}"
                                {{ $assignment->course_id == $course->id ? 'selected' : '' }}>
                                {{ $course->course_name }}
                            </option>
                        @endforeach
                    @else
                        <option value="">— Select Department first —</option>
                    @endif
                </select>

                <button type="button" class="btn-remove-assignment"
                    style="{{ count($existingAssignments) === 1 ? 'visibility:hidden' : '' }}">✕</button>
            </div>
        @endforeach
        </div>

        <button type="button" id="add-assignment" class="btn btn-secondary" style="margin-top:10px">
            + Add Another Course
        </button>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
    </form>
</div>

<style>
.assignment-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 8px;
}
.assignment-row select { flex: 1; }
.btn-remove-assignment {
    background: #fff5f5;
    border: 1.5px solid #ffc9c9;
    color: #e03131;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 700;
    transition: background 0.15s;
}
.btn-remove-assignment:hover { background: #ffe3e3; }
</style>

<script>
const courseMap = {
    @foreach($departments as $dept)
    "{{ $dept->id }}": [
        @foreach($dept->courses as $course)
        { id: "{{ $course->id }}", name: "{{ $course->course_name }}" },
        @endforeach
    ],
    @endforeach
};

let rowIndex = {{ count($existingAssignments) }};

function bindRow(row) {
    const deptSel   = row.querySelector('.dept-select');
    const courseSel = row.querySelector('.course-select');
    const removeBtn = row.querySelector('.btn-remove-assignment');

    deptSel.addEventListener('change', () => {
        const courses = courseMap[deptSel.value] ?? [];
        courseSel.innerHTML = courses.length
            ? '<option value="">— Select Course —</option>'
            : '<option value="">— No courses —</option>';
        courses.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            courseSel.appendChild(opt);
        });
    });

    removeBtn.addEventListener('click', () => {
        row.remove();
        updateRemoveButtons();
    });
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.assignment-row');
    rows.forEach(r => {
        r.querySelector('.btn-remove-assignment').style.visibility =
            rows.length === 1 ? 'hidden' : 'visible';
    });
}

document.querySelectorAll('.assignment-row').forEach(bindRow);

document.getElementById('add-assignment').addEventListener('click', () => {
    const list = document.getElementById('assignments-list');
    const row  = document.createElement('div');
    row.className = 'assignment-row';
    row.dataset.index = rowIndex;
    row.innerHTML = `
        <select name="assignments[${rowIndex}][department_id]" class="dept-select" required>
            <option value="">— Department —</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
            @endforeach
        </select>
        <select name="assignments[${rowIndex}][course_id]" class="course-select" required>
            <option value="">— Select Department first —</option>
        </select>
        <button type="button" class="btn-remove-assignment">✕</button>
    `;
    list.appendChild(row);
    bindRow(row);
    updateRemoveButtons();
    rowIndex++;
});
</script>
@endsection