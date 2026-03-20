@extends('layouts.admin')
@section('title','New Teacher')
@section('page-title','New Teacher')
@section('content')
 
<div class="form-card" style="max-width:820px">
    <form method="POST" action="{{ route('admin.teachers.store') }}">
    @csrf
 
    {{-- Teacher info --}}
    <div class="section-label">Teacher information</div>
 
    <div class="field-row">
        <div class="field">
            <label>Full name <span class="req">*</span></label>
            <input type="text" name="teacher_name"
                   value="{{ old('teacher_name') }}"
                   placeholder="e.g. Juan dela Cruz" required>
            @error('teacher_name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Teacher code <span class="req">*</span></label>
            <input type="text" name="teacher_code"
                   value="{{ old('teacher_code') }}"
                   placeholder="e.g. 10234"
                   pattern="[0-9]+"
                   title="Numeric digits only"
                   required>
            @error('teacher_code')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>
 
    <div class="field">
        <label>Email address <span style="color:var(--text-soft);font-weight:400">(optional)</span></label>
        <input type="email" name="email"
               value="{{ old('email') }}"
               placeholder="e.g. juan@school.com">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
    </div>
 
    {{-- Subject assignments --}}
    <div class="section-label" style="margin-top:8px">
        Subject assignments
        <span style="font-weight:400;color:var(--text-soft)">— optional, can be done later</span>
    </div>
 
    <div id="subject-rows">
        @include('admin.teachers._subject_row', ['index' => 0, 'subjects' => $subjects, 'semesters' => $semesters])
    </div>
 
    <button type="button" onclick="addRow()"
            class="btn btn-secondary"
            style="font-size:12px;padding:7px 14px;margin-bottom:20px">
        + Add another subject
    </button>
 
    <div class="form-actions">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create teacher</button>
    </div>
    </form>
</div>
 
<style>
.section-label { font-size:13px; font-weight:600; color:var(--text-dark); margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid var(--border); }
.subject-row-grid { display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:12px; margin-bottom:10px; align-items:end; }
</style>
 
<script>
let rowIndex = 1;
const subjects  = @json($subjects->map(fn($s) => ['id' => $s->id, 'label' => $s->subject_code . ' — ' . $s->subject_name . ' (' . $s->department->department_name . ')']));
const semesters = @json($semesters->map(fn($s) => ['id' => $s->id, 'label' => $s->semester_name . ' Sem — S.Y. ' . $s->schoolYear->year_start . '–' . $s->schoolYear->year_end]));
 
function opts(items) {
    return '<option value="">— Select —</option>' +
        items.map(i => `<option value="${i.id}">${i.label}</option>`).join('');
}
 
function addRow() {
    const wrap = document.getElementById('subject-rows');
    const div  = document.createElement('div');
    div.className = 'subject-row-grid';
    div.innerHTML = `
        <div class="field" style="margin:0">
            <label>Subject</label>
            <select name="subjects[${rowIndex}][subject_id]">${opts(subjects)}</select>
        </div>
        <div class="field" style="margin:0">
            <label>Semester</label>
            <select name="subjects[${rowIndex}][semester_id]">${opts(semesters)}</select>
        </div>
        <div class="field" style="margin:0">
            <label>Section</label>
            <input type="text" name="subjects[${rowIndex}][section]" placeholder="e.g. BSIT 1-A">
        </div>
        <div style="padding-bottom:2px">
            <button type="button" onclick="this.closest('.subject-row-grid').remove()"
                    style="background:none;border:none;cursor:pointer;color:var(--red);font-size:20px;padding:6px 4px;line-height:1">×</button>
        </div>`;
    wrap.appendChild(div);
    rowIndex++;
}
</script>
@endsection