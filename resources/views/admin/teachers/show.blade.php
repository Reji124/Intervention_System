@extends('layouts.admin')
@section('title','Manage Teacher')
@section('page-title','Manage Teacher')
@section('content')
 
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
    <div>
        <h2 style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text-dark)">
            {{ $teacher->teacher_name }}
        </h2>
        <div style="display:flex;align-items:center;gap:16px;margin-top:6px;flex-wrap:wrap">
            <span style="font-size:12px;color:var(--text-soft)">
                Code: <strong style="color:var(--text-dark)">{{ $teacher->teacher_code ?? '—' }}</strong>
            </span>
            @if($teacher->email)
            <span style="font-size:12px;color:var(--text-soft)">
                Email: <strong style="color:var(--text-dark)">{{ $teacher->email }}</strong>
            </span>
            @endif
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn-link" style="font-size:12px">
                Edit info
            </a>
        </div>
    </div>
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">← Back</a>
</div>
 
{{-- Assign subject form --}}
<div class="form-card" style="margin-bottom:20px">
    <div style="font-size:13px;font-weight:600;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)">
        Assign subject
    </div>
    <form method="POST" action="{{ route('admin.teachers.assign-subject', $teacher) }}">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end">
        <div class="field" style="margin:0">
            <label>Subject</label>
            <select name="subject_id" required>
                <option value="">— Select subject —</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}">
                    {{ $subject->subject_code }} — {{ $subject->subject_name }}
                    ({{ $subject->department->department_name }})
                </option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin:0">
            <label>Semester</label>
            <select name="semester_id" required>
                <option value="">— Select semester —</option>
                @foreach($semesters as $sem)
                <option value="{{ $sem->id }}">
                    {{ $sem->semester_name }} Sem —
                    S.Y. {{ $sem->schoolYear->year_start }}–{{ $sem->schoolYear->year_end }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin:0">
            <label>Section</label>
            <input type="text" name="section" placeholder="e.g. BSIT 1-A" required>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="width:100%">Assign</button>
        </div>
    </div>
    </form>
</div>
 
{{-- Subjects list --}}
<div class="card">
    <div style="padding:16px 22px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <span style="font-family:'DM Serif Display',serif;font-size:16px;color:var(--text-dark)">
            Assigned subjects
        </span>
        <span style="font-size:12px;color:var(--text-soft)">
            {{ $teacher->teacherSubjects->count() }} total
        </span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject name</th>
                <th>Section</th>
                <th>Semester</th>
                <th>Department</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($teacher->teacherSubjects as $ts)
        <tr>
            <td><span class="badge badge-mid">{{ $ts->subject->subject_code }}</span></td>
            <td><span class="td-main">{{ $ts->subject->subject_name }}</span></td>
            <td>{{ $ts->section }}</td>
            <td>
                {{ $ts->semester->semester_name }} Sem,
                S.Y. {{ $ts->semester->schoolYear->year_start }}–{{ $ts->semester->schoolYear->year_end }}
            </td>
            <td style="font-size:12px;color:var(--text-soft)">
                {{ $ts->subject->department->department_name }}
            </td>
            <td>
                <form method="POST"
                      action="{{ route('admin.teachers.remove-subject', $ts) }}"
                      onsubmit="return confirm('Remove {{ $ts->subject->subject_code }} from {{ $teacher->teacher_name }}?')">
                    @csrf @method('DELETE')
                    <button class="btn-link-danger">Remove</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="empty-cell">
                No subjects assigned yet. Use the form above to assign one.
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection