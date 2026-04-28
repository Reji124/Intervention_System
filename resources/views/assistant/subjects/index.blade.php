@extends('layouts.assistant')
@section('title', 'All Subjects')
@section('page-title', 'All Subjects')

@push('styles')
<style>
    /* ── Filter bar ── */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .filter-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: var(--text-soft);
        margin-right: 2px;
    }
    .filter-bar select {
        padding: 9px 36px 9px 14px;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23aaa' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 12px center;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        color: var(--text-dark);
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
        min-width: 160px;
    }
    .filter-bar select:hover  { border-color: var(--teal-light); }
    .filter-bar select:focus  { border-color: var(--teal-light); box-shadow: 0 0 0 3px rgba(28,163,117,.12); }

    /* ── Results count ── */
    .results-count {
        margin-left: auto;
        font-size: 12px;
        color: var(--text-soft);
        font-style: italic;
    }

    /* ── Teacher section ── */
    .teacher-section  { margin-bottom: 36px; }
    .teacher-heading  {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border);
    }
    .teacher-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: color-mix(in srgb, var(--teal-light) 12%, transparent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: var(--teal-light);
        flex-shrink: 0;
    }
    .teacher-info { display: flex; flex-direction: column; gap: 1px; }
    .teacher-name {
        font-family: 'DM Serif Display', serif;
        font-size: 17px;
        color: var(--text-dark);
        line-height: 1.2;
    }
    .teacher-subject-count {
        font-size: 11px;
        color: var(--text-soft);
    }

    /* ── Subject grid ── */
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 14px;
    }

    /* ── Subject card ── */
    .subject-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        transition: border-color .18s, transform .18s, box-shadow .18s;
        position: relative;
    }
    .subject-card:hover {
        border-color: var(--teal-light);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,.07);
    }

    /* colour accent bar on top of card */
    .card-accent {
        height: 4px;
        background: linear-gradient(90deg, var(--teal-light), color-mix(in srgb, var(--teal-light) 40%, #a8eddc));
        flex-shrink: 0;
    }

    .card-body { padding: 16px 18px 14px; flex: 1; }

    .subject-code {
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--teal-light);
        background: color-mix(in srgb, var(--teal-light) 10%, transparent);
        padding: 2px 8px;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    .subject-name {
        font-family: 'DM Serif Display', serif;
        font-size: 15.5px;
        color: var(--text-dark);
        line-height: 1.3;
        margin-bottom: 5px;
    }
    .subject-meta {
        font-size: 11.5px;
        color: var(--text-soft);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .meta-dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: var(--border);
        flex-shrink: 0;
    }
    .subject-course {
        font-size: 11px;
        color: var(--text-soft);
        margin-top: 4px;
        font-style: italic;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Stats row ── */
    .card-stats {
        display: flex;
        gap: 0;
        border-top: 1.5px solid var(--border);
    }
    .cs-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 12px;
        gap: 2px;
    }
    .cs-item + .cs-item {
        border-left: 1.5px solid var(--border);
    }
    .cs-val {
        font-family: 'DM Serif Display', serif;
        font-size: 22px;
        color: var(--text-dark);
        line-height: 1;
    }
    .cs-val.fail { color: var(--red, #e05555); }
    .cs-label {
        font-size: 9.5px;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: var(--text-soft);
    }

    /* ── Card footer ── */
    .card-footer {
        padding: 9px 18px;
        background: #faf9f7;
        border-top: 1.5px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
        letter-spacing: .3px;
    }
    .badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .badge-1st {
        background: #fef3e2;
        color: #c47a15;
    }
    .badge-1st::before { background: #f0a500; }
    .badge-2nd {
        background: #e8f0fe;
        color: #1a56a8;
    }
    .badge-2nd::before { background: #3b82f6; }

    .view-link {
        font-size: 12px;
        font-weight: 600;
        color: var(--teal-light);
        display: flex;
        align-items: center;
        gap: 4px;
        transition: gap .15s;
    }
    .subject-card:hover .view-link { gap: 7px; }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 72px 20px;
        color: var(--text-soft);
    }
    .empty-state-icon {
        width: 52px;
        height: 52px;
        margin: 0 auto 18px;
        color: var(--border);
    }
    .empty-state p { font-size: 14px; }

    /* ── No-results (JS-injected) ── */
    .no-results-msg {
        display: none;
        text-align: center;
        padding: 40px 20px;
        font-size: 13px;
        color: var(--text-soft);
        font-style: italic;
    }
</style>
@endpush

@section('content')

<div class="filter-bar">
    <span class="filter-label">Filter</span>

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

    <span class="results-count" id="results-count"></span>
</div>

<div id="no-results" class="no-results-msg">No subjects match the selected filters.</div>

@if($grouped->isEmpty())
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
        </svg>
        <p>No subjects assigned to any teacher yet.</p>
    </div>
@else
    @foreach($grouped as $teacherName => $subjects)
    <div class="teacher-section"
         data-teacher="{{ $subjects->first()->teacher_id }}">

        <div class="teacher-heading">
            <div class="teacher-avatar">
                {{ strtoupper(substr(explode(' ', $teacherName)[0], 0, 1)) }}{{ strtoupper(substr(explode(' ', $teacherName)[count(explode(' ', $teacherName)) - 1], 0, 1)) }}
            </div>
            <div class="teacher-info">
                <span class="teacher-name">{{ $teacherName }}</span>
                <span class="teacher-subject-count">{{ $subjects->count() }} {{ Str::plural('subject', $subjects->count()) }}</span>
            </div>
        </div>

        <div class="subjects-grid">
            @foreach($subjects as $ts)
            <a href="{{ route('assistant.subjects.show', $ts) }}"
               class="subject-card"
               data-course="{{ $ts->subject->courses->first()?->id }}">

                <div class="card-accent"></div>

                <div class="card-body">
                    <div class="subject-code">{{ $ts->subject->subject_code }}</div>
                    <div class="subject-name">{{ $ts->subject->subject_name }}</div>
                    <div class="subject-meta">
                        <span>{{ $ts->section }}</span>
                        <span class="meta-dot"></span>
                        <span>Year {{ $ts->subject->year_level }}</span>
                    </div>
                    <div class="subject-course" title="{{ $ts->subject->course->course_name }}">
                        {{ $ts->subject->course->course_name }}
                    </div>
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
                    @php
                        $semName = $ts->semester->semester_name;
                        $is1st   = str_contains($semName, '1');
                    @endphp
                    <span class="badge {{ $is1st ? 'badge-1st' : 'badge-2nd' }}">
                        {{ $semName }}
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
    let visibleCards = 0;

    document.querySelectorAll('.teacher-section').forEach(section => {
        const teacherMatch = !teacherId || section.dataset.teacher === teacherId;
        let sectionVisible = false;

        section.querySelectorAll('.subject-card').forEach(card => {
            const courseMatch = !courseId || card.dataset.course === courseId;
            const show = teacherMatch && courseMatch;
            card.style.display = show ? 'flex' : 'none';
            if (show) { sectionVisible = true; visibleCards++; }
        });

        section.style.display = sectionVisible ? 'block' : 'none';
    });

    const noRes = document.getElementById('no-results');
    const countEl = document.getElementById('results-count');

    noRes.style.display = visibleCards === 0 ? 'block' : 'none';
    countEl.textContent = (teacherId || courseId)
        ? visibleCards + ' subject' + (visibleCards !== 1 ? 's' : '') + ' found'
        : '';
}
</script>
@endpush