@extends('layouts.assistant')
@section('title', 'All Subjects')
@section('page-title', 'All Subjects')

@push('styles')
<style>
    .filter-bar { display:flex; align-items:center; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
    .filter-bar select { padding:8px 12px; font-family:'DM Sans',sans-serif; font-size:13px; background:#fff; border:1.5px solid var(--border); border-radius:8px; color:var(--text-dark); outline:none; }
    .teacher-section { margin-bottom:28px; }
    .teacher-heading { font-family:'DM Serif Display',serif; font-size:17px; color:var(--text-dark); margin-bottom:12px; padding-bottom:8px; border-bottom:2px solid var(--border); display:flex; align-items:center; gap:10px; }
    .subjects-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
    .subject-card { background:#fff; border:1px solid var(--border); border-radius:10px; overflow:hidden; text-decoration:none; display:block; transition:all .2s; }
    .subject-card:hover { border-color:var(--teal-light); transform:translateY(-2px); }
    .card-top { padding:16px 18px 12px; border-bottom:1px solid var(--border); }
    .subject-code { font-size:11px; font-weight:600; letter-spacing:.8px; text-transform:uppercase; color:var(--teal-light); margin-bottom:3px; }
    .subject-name { font-family:'DM Serif Display',serif; font-size:16px; color:var(--text-dark); margin-bottom:3px; }
    .subject-meta { font-size:11px; color:var(--text-soft); }
    .subject-course { font-size:11px; color:var(--text-soft); margin-top:2px; font-style:italic; }
    .card-stats { display:grid; grid-template-columns:1fr 1fr; padding:10px 18px; gap:8px; }    .cs-item { display:flex; flex-direction:column; gap:2px; }
    .cs-val { font-family:'DM Serif Display',serif; font-size:18px; color:var(--text-dark); }
    .cs-val.fail { color:var(--red); }
    .cs-label { font-size:10px; text-transform:uppercase; letter-spacing:.6px; color:var(--text-soft); }
    .card-footer { padding:8px 18px; background:#faf8f5; display:flex; align-items:center; justify-content:space-between; }
    .badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-1st { background:var(--amber-bg); color:var(--amber); }
    .badge-2nd { background:var(--blue-bg); color:var(--blue); }
    .view-link { font-size:12px; color:var(--teal-light); font-weight:500; }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-soft); }
    .empty-state svg { width:48px; height:48px; color:var(--border); margin:0 auto 16px; display:block; }
</style>
@endpush

@section('content')

<div class="filter-bar">
    <select id="teacher-filter" onchange="applyFilters()">
        <option value="">All teachers</option>
        @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}">{{ $teacher->teacher_name }}</option>
        @endforeach
    </select>

    <select id="course-filter" onchange="applyFilters()">
        <option value="">All courses</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}">{{ $course->course_name }}</option>
        @endforeach
    </select>
</div>

@if($grouped->isEmpty())
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
        </svg>
        <p>No subjects assigned to any teacher yet.</p>
    </div>
@else
    @foreach($grouped as $teacherName => $subjects)
    <div class="teacher-section" data-teacher="{{ $subjects->first()->teacher_id }}">
        <div class="teacher-heading">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 style="width:18px;height:18px;color:var(--teal-light)">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            {{ $teacherName }}
        </div>
        <div class="subjects-grid">
            @foreach($subjects as $ts)
            <a href="{{ route('assistant.subjects.show', $ts) }}"
               class="subject-card"
               data-course="{{ $ts->subject->course_id }}">
                <div class="card-top">
                    <div class="subject-code">{{ $ts->subject->subject_code }}</div>
                    <div class="subject-name">{{ $ts->subject->subject_name }}</div>
                    <div class="subject-meta">{{ $ts->section }} · Year {{ $ts->subject->year_level }}</div>
                    <div class="subject-course">{{ $ts->subject->course->course_name }}</div>
                </div>
                <div class="card-stats">
                <div class="cs-item">
                    <span class="cs-val">{{ $studentCounts[$ts->id] ?? 0 }}</span>
                    <span class="cs-label">Students</span>
                </div>
                <div class="cs-item">
                    <span class="cs-val">{{ $ts->exams->count() }}</span>
                    <span class="cs-label">Exams</span>
                </div>
                </div>
                <div class="card-footer">
                    <span class="badge badge-{{ $ts->semester->semester_name === '1st' ? '1st' : '2nd' }}">
                        {{ $ts->semester->semester_name }} Sem
                    </span>
                    <span class="view-link">View →</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
@endif

@endsection

@push('scripts')
<script>
function applyFilters() {
    const teacherId = document.getElementById('teacher-filter').value;
    const courseId  = document.getElementById('course-filter').value;

    document.querySelectorAll('.teacher-section').forEach(section => {
        const teacherMatch = !teacherId || section.dataset.teacher === teacherId;
        let sectionHasVisible = false;

        section.querySelectorAll('.subject-card').forEach(card => {
            const courseMatch = !courseId || card.dataset.course === courseId;
            const visible = teacherMatch && courseMatch;
            card.style.display = visible ? 'block' : 'none';
            if (visible) sectionHasVisible = true;
        });

        // Hide the whole teacher section if no cards are visible
        section.style.display = (teacherMatch && sectionHasVisible) ? 'block' : 'none';
    });
}
</script>
@endpush