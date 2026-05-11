@extends('layouts.admin')
@section('title', 'Manage ' . $schoolYear->year_label)
@section('page-title', 'Manage ' . $schoolYear->year_label)

@section('content')

<style>
    /* ── Page header ─────────────────────────────────────────────────────────── */
    .manage-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .manage-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-soft);
        text-decoration: none;
        font-weight: 500;
        transition: color .15s;
    }
    .back-link:hover { color: var(--text-dark); text-decoration: none; }
    .page-title-sm {
        font-family: 'DM Serif Display', serif;
        font-size: 22px;
        color: var(--text-dark);
        line-height: 1;
    }
    .sy-meta {
        font-size: 12px;
        color: var(--text-soft);
        margin-top: 4px;
    }

    /* ── Semester tabs ───────────────────────────────────────────────────────── */
    .sem-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 2px solid var(--border);
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .sem-tab {
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-soft);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color .15s, border-color .15s;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        outline: none;
    }
    .sem-tab:hover  { color: var(--text-dark); }
    .sem-tab.active { color: var(--navy); border-bottom-color: var(--navy); }

    /* ── Semester panel ──────────────────────────────────────────────────────── */
    .sem-panel { display: none; }
    .sem-panel.active { display: block; }

    /* ── Stat row inside a panel ─────────────────────────────────────────────── */
    .panel-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .panel-stat {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px 18px;
    }
    .panel-stat-value {
        font-family: 'DM Serif Display', serif;
        font-size: 26px;
        color: var(--text-dark);
        line-height: 1;
        margin-bottom: 4px;
    }
    .panel-stat-label { font-size: 11px; color: var(--text-soft); }

    /* ── Card ────────────────────────────────────────────────────────────────── */
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
    .card-header { padding: 16px 20px 12px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .card-title { font-family: 'DM Serif Display', serif; font-size: 15px; color: var(--text-dark); }

    /* ── Table ───────────────────────────────────────────────────────────────── */
    table { width: 100%; border-collapse: collapse; }
    thead th {
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .8px; color: var(--text-soft);
        padding: 10px 18px; text-align: left;
        background: #faf8f5; border-bottom: 1px solid var(--border);
    }
    tbody td {
        padding: 11px 18px; font-size: 13px;
        border-bottom: 1px solid #f3efe8; color: var(--text-mid);
        vertical-align: middle;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #faf8f5; }
    .td-main { font-weight: 500; color: var(--text-dark); }
    .td-sub  { font-size: 11px; color: var(--text-soft); margin-top: 2px; }

    /* Pass rate bar */
    .rate-wrap  { display: flex; align-items: center; gap: 8px; }
    .rate-bar   { flex: 1; height: 5px; border-radius: 99px; background: #eee; overflow: hidden; min-width: 50px; }
    .rate-fill  { height: 100%; border-radius: 99px; }
    .rate-fill.good { background: var(--green); }
    .rate-fill.warn { background: var(--amber); }
    .rate-fill.risk { background: var(--red); }
    .rate-lbl   { font-size: 12px; font-weight: 600; white-space: nowrap; }
    .rate-lbl.good { color: var(--green); }
    .rate-lbl.warn { color: var(--amber); }
    .rate-lbl.risk { color: var(--red); }

    /* Badges */
    .badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
    .badge-pass   { background: var(--green-bg); color: var(--green); }
    .badge-risk   { background: var(--red-bg);   color: var(--red); }
    .badge-nodata { background: #f0f0f0;          color: #999; }

    /* Empty state */
    .empty-cell { text-align: center; color: var(--text-soft); padding: 32px !important; font-size: 13px; }
</style>

{{-- ── PAGE HEADER ──────────────────────────────────────────────────────────── --}}
<div class="manage-header">
    <div class="manage-header-left">
        <a href="{{ route('admin.school-years.index') }}" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            School Years
        </a>
        <div>
            <div class="page-title-sm">{{ $schoolYear->year_label }}</div>
            <div class="sy-meta">{{ $schoolYear->semesters->count() }} semester(s) · created {{ $schoolYear->created_at->format('M d, Y') }}</div>
        </div>
    </div>
    <a href="{{ route('admin.school-years.edit', $schoolYear) }}" class="btn btn-secondary" style="font-size:13px">
        Edit School Year
    </a>
</div>

@if($schoolYear->semesters->isEmpty())
    <div class="card" style="padding:40px;text-align:center;color:var(--text-soft);font-size:13px">
        No semesters found for this school year.
    </div>
@else

{{-- ── SEMESTER TABS ────────────────────────────────────────────────────────── --}}
<div class="sem-tabs">
    @foreach($schoolYear->semesters as $i => $sem)
        <button class="sem-tab {{ $i === 0 ? 'active' : '' }}"
            onclick="switchTab({{ $sem->id }}, this)">
            {{ $sem->semester_name }}
        </button>
    @endforeach
</div>

{{-- ── SEMESTER PANELS ──────────────────────────────────────────────────────── --}}
@foreach($schoolYear->semesters as $i => $sem)
@php
    $subjects      = $sem->teacherSubjects->load(['teacher', 'subject']);
    $totalSubjects = $subjects->count();
    $totalTeachers = $subjects->pluck('teacher_id')->unique()->count();

    // Compute pass rates per teacher-subject
    $subjectStats = $subjects->map(function ($ts) {
        $total    = $ts->examResults->count();
        $pass     = $ts->examResults->where('remark', 'pass')->count();
        $passRate = $total > 0 ? (int) round(($pass / $total) * 100) : null;
        return [
            'ts'        => $ts,
            'total'     => $total,
            'pass'      => $pass,
            'pass_rate' => $passRate,
        ];
    });

    $atRisk = $subjectStats->filter(fn($s) => $s['pass_rate'] !== null && $s['pass_rate'] < 60)->count();
@endphp

<div class="sem-panel {{ $i === 0 ? 'active' : '' }}" id="panel-{{ $sem->id }}">

    {{-- Mini stats --}}
    <div class="panel-stats">
        <div class="panel-stat">
            <div class="panel-stat-value">{{ $totalSubjects }}</div>
            <div class="panel-stat-label">Subjects</div>
        </div>
        <div class="panel-stat">
            <div class="panel-stat-value">{{ $totalTeachers }}</div>
            <div class="panel-stat-label">Teachers</div>
        </div>
        <div class="panel-stat">
            <div class="panel-stat-value" style="{{ $atRisk > 0 ? 'color:var(--red)' : '' }}">{{ $atRisk }}</div>
            <div class="panel-stat-label">Subjects at Risk</div>
        </div>
    </div>

    {{-- Subjects table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Subjects &amp; Pass Rates</span>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary" style="font-size:12px;padding:6px 14px">
                + Add Subject
            </a>
        </div>
        <div class="card-table">
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Students</th>
                    <th>Pass Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjectStats as $stat)
                @php
                    $rate      = $stat['pass_rate'];
                    $fillClass = $rate >= 75 ? 'good' : ($rate >= 60 ? 'warn' : 'risk');
                @endphp
                <tr>
                    <td>
                        <div class="td-main">{{ $stat['ts']->subject->subject_code ?? '—' }}</div>
                        <div class="td-sub">{{ $stat['ts']->subject->subject_name ?? '' }}</div>
                    </td>
                    <td>
                        <div class="td-main">{{ $stat['ts']->teacher->teacher_name ?? '—' }}</div>
                    </td>
                    <td>{{ $stat['total'] }}</td>
                    <td>
                        @if($rate === null)
                            <span style="font-size:12px;color:var(--text-soft)">No data</span>
                        @else
                            <div class="rate-wrap">
                                <div class="rate-bar">
                                    <div class="rate-fill {{ $fillClass }}" style="width:{{ $rate }}%"></div>
                                </div>
                                <span class="rate-lbl {{ $fillClass }}">{{ $rate }}%</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($rate === null)
                            <span class="badge badge-nodata">No exams</span>
                        @elseif($rate < 60)
                            <span class="badge badge-risk">At Risk</span>
                        @else
                            <span class="badge badge-pass">On Track</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty-cell">No subjects assigned to this semester yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

</div>{{-- /sem-panel --}}
@endforeach

@endif {{-- semesters not empty --}}

@push('scripts')
<script>
    function switchTab(semId, btn) {
        document.querySelectorAll('.sem-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.sem-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('panel-' + semId).classList.add('active');
    }
</script>
@endpush

@endsection