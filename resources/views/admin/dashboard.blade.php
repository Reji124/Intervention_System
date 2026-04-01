{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── Stat cards ─────────────────────────────────────────────────────────── */
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
    .stat-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:20px 22px; position:relative; overflow:hidden; animation:slideUp .4s ease both; }
    .stat-card:nth-child(1){animation-delay:.05s} .stat-card:nth-child(2){animation-delay:.10s}
    .stat-card:nth-child(3){animation-delay:.15s} .stat-card:nth-child(4){animation-delay:.20s}
    @keyframes slideUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:12px 12px 0 0; }
    .c-blue::before{background:#4f8ef7} .c-green::before{background:var(--green)}
    .c-gold::before{background:var(--gold)} .c-red::before{background:var(--red)}
    .stat-change { position:absolute; top:14px; right:14px; font-size:10px; font-weight:600; padding:2px 7px; border-radius:20px; }
    .stat-change.up { background:var(--green-bg); color:var(--green); }
    .stat-change.down { background:var(--red-bg); color:var(--red); }
    .stat-change.neutral { background:var(--amber-bg); color:var(--amber); }
    .stat-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; margin-bottom:14px; }
    .stat-icon svg { width:18px; height:18px; }
    .c-blue .stat-icon{background:#eef3fe;color:#4f8ef7}
    .c-green .stat-icon{background:var(--green-bg);color:var(--green)}
    .c-gold .stat-icon{background:var(--amber-bg);color:var(--amber)}
    .c-red .stat-icon{background:var(--red-bg);color:var(--red)}
    .stat-value { font-family:'DM Serif Display',serif; font-size:32px; line-height:1; color:var(--text-dark); margin-bottom:4px; }
    .stat-label { font-size:12px; color:var(--text-soft); }

    /* ── Layout ─────────────────────────────────────────────────────────────── */
    .main-grid { display:grid; grid-template-columns:1fr 320px; gap:20px; margin-bottom:20px; }
    .full-width { margin-bottom:20px; }
    .card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; animation:slideUp .4s ease .2s both; }
    .card-header { padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .card-header-left { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .card-title { font-family:'DM Serif Display',serif; font-size:16px; color:var(--text-dark); }
    .card-action { font-size:12px; color:var(--teal-light); text-decoration:none; font-weight:500; white-space:nowrap; }

    /* ── Semester filter ────────────────────────────────────────────────────── */
    .sem-select { font-size:12px; font-weight:500; color:var(--text-mid); border:1px solid var(--border); border-radius:7px; padding:5px 10px; background:var(--card-bg); cursor:pointer; outline:none; }

    /* ── Teacher performance table ──────────────────────────────────────────── */
    table { width:100%; border-collapse:collapse; }
    thead th { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.8px; color:var(--text-soft); padding:10px 18px; text-align:left; background:#faf8f5; border-bottom:1px solid var(--border); }
    tbody td { padding:11px 18px; font-size:13px; border-bottom:1px solid #f3efe8; color:var(--text-mid); vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#faf8f5; }
    .td-main { font-weight:500; color:var(--text-dark); }
    .td-sub { font-size:11px; color:var(--text-soft); margin-top:2px; }

    /* Pass rate bar */
    .rate-wrap { display:flex; align-items:center; gap:8px; }
    .rate-bar { flex:1; height:6px; border-radius:99px; background:#eee; overflow:hidden; min-width:60px; }
    .rate-fill { height:100%; border-radius:99px; transition:width .4s ease; }
    .rate-fill.good  { background:var(--green); }
    .rate-fill.warn  { background:var(--amber); }
    .rate-fill.risk  { background:var(--red); }
    .rate-label { font-size:12px; font-weight:600; white-space:nowrap; }
    .rate-label.good { color:var(--green); }
    .rate-label.warn { color:var(--amber); }
    .rate-label.risk { color:var(--red); }

    .risk-badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; background:var(--red-bg); color:var(--red); }
    .ok-badge   { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; background:var(--green-bg); color:var(--green); }

    /* ── Exam type breakdown ────────────────────────────────────────────────── */
    .breakdown-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:0; }
    .breakdown-item { padding:20px 22px; border-right:1px solid var(--border); }
    .breakdown-item:last-child { border-right:none; }
    .breakdown-type { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.8px; color:var(--text-soft); margin-bottom:12px; }
    .breakdown-pass-rate { font-family:'DM Serif Display',serif; font-size:28px; color:var(--text-dark); margin-bottom:6px; }
    .breakdown-bar { height:6px; border-radius:99px; background:#eee; overflow:hidden; margin-bottom:8px; }
    .breakdown-bar-fill { height:100%; border-radius:99px; }
    .breakdown-meta { display:flex; justify-content:space-between; font-size:11px; color:var(--text-soft); }
    .breakdown-meta span { display:flex; align-items:center; gap:4px; }
    .dot { width:7px; height:7px; border-radius:50%; display:inline-block; }
    .dot-green { background:var(--green); }
    .dot-red   { background:var(--red); }

    /* ── Subjects at risk ───────────────────────────────────────────────────── */
    .risk-list { padding:8px 0; }
    .risk-row { display:flex; align-items:center; justify-content:space-between; padding:10px 22px; border-bottom:1px solid #f3efe8; gap:10px; }
    .risk-row:last-child { border-bottom:none; }
    .risk-subject { font-size:13px; font-weight:500; color:var(--text-dark); }
    .risk-teacher { font-size:11px; color:var(--text-soft); margin-top:2px; }
    .risk-rate { font-size:12px; font-weight:700; color:var(--red); white-space:nowrap; }

    /* ── Quick actions ──────────────────────────────────────────────────────── */
    .quick-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:16px; }
    .quick-btn { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; padding:16px 10px; border-radius:10px; background:#faf8f5; border:1px solid var(--border); font-size:12px; font-weight:500; color:var(--text-mid); text-decoration:none; transition:all .15s; text-align:center; }
    .quick-btn:hover { background:var(--navy); color:#fff; border-color:var(--navy); }
    .quick-btn svg { width:20px; height:20px; }

    /* ── Empty state ────────────────────────────────────────────────────────── */
    .empty-row td { text-align:center; color:var(--text-soft); padding:32px !important; font-size:13px; }

    .badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-pass   { background:var(--green-bg); color:var(--green); }
    .badge-fail   { background:var(--red-bg);   color:var(--red); }
    .badge-prelim { background:var(--amber-bg); color:var(--amber); }
    .badge-mid,.badge-midterm { background:var(--blue-bg); color:var(--blue); }
    .badge-final  { background:#f0ebfa; color:#534ab7; }
</style>
@endpush

@section('content')

{{-- ── STAT CARDS ──────────────────────────────────────────────────────────── --}}
<div class="stats-grid">

    <div class="stat-card c-blue">
        <span class="stat-change up">+{{ $newTeachersThisMonth ?? 0 }} this month</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalTeachers ?? 0 }}</div>
        <div class="stat-label">Total Teachers</div>
    </div>

    <div class="stat-card c-green">
        <span class="stat-change up">{{ $overallPassRate ?? 0 }}% pass rate</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalExamsUploaded ?? 0 }}</div>
        <div class="stat-label">Exams Uploaded</div>
    </div>

    <div class="stat-card c-red">
        <span class="stat-change down">Needs attention</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="stat-value">{{ $failingStudents ?? 0 }}</div>
        <div class="stat-label">Failing Students</div>
    </div>

    <div class="stat-card c-gold">
        <span class="stat-change neutral">At risk</span>
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="stat-value">{{ $atRiskTeachers ?? 0 }}</div>
        <div class="stat-label">Teachers at Risk</div>
    </div>

</div>

{{-- ── EXAM TYPE BREAKDOWN ──────────────────────────────────────────────────── --}}
<div class="card full-width">
    <div class="card-header">
        <span class="card-title">Exam Type Breakdown</span>
        <span style="font-size:12px;color:var(--text-soft)">Pass rate per exam type — all teachers</span>
    </div>
    <div class="breakdown-grid">
        @foreach(['prelim' => 'Prelim', 'midterm' => 'Midterm', 'final' => 'Final'] as $type => $label)
        @php
            $data      = $examBreakdown[$type] ?? ['pass_rate' => 0, 'pass' => 0, 'fail' => 0, 'total' => 0];
            $rate      = $data['pass_rate'];
            $fillClass = $rate >= 75 ? 'good' : ($rate >= 60 ? 'warn' : 'risk');
            $fillColor = $rate >= 75 ? 'var(--green)' : ($rate >= 60 ? 'var(--amber)' : 'var(--red)');
        @endphp
        <div class="breakdown-item">
            <div class="breakdown-type">{{ $label }}</div>
            <div class="breakdown-pass-rate">{{ $rate }}%</div>
            <div class="breakdown-bar">
                <div class="breakdown-bar-fill" style="width:{{ $rate }}%; background:{{ $fillColor }}"></div>
            </div>
            <div class="breakdown-meta">
                <span><span class="dot dot-green"></span>{{ $data['pass'] }} passed</span>
                <span><span class="dot dot-red"></span>{{ $data['fail'] }} failed</span>
                <span>{{ $data['total'] }} total</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── MAIN GRID ────────────────────────────────────────────────────────────── --}}
<div class="main-grid">

    {{-- Teacher Performance Table --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <span class="card-title">Teacher Performance</span>
                <select class="sem-select" onchange="filterBySemester(this.value)">
                    <option value="all">All Semesters</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>
                            {{ $sem->semester_name }} — {{ $sem->schoolYear->year_label ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('admin.interventions.index') }}" class="card-action">Full report →</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Subjects</th>
                    <th>Students</th>
                    <th>Pass Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teacherPerformance as $t)
                @php
                    $rate      = $t->pass_rate ?? 0;
                    $fillClass = $rate >= 75 ? 'good' : ($rate >= 60 ? 'warn' : 'risk');
                    $isAtRisk  = $rate < 60;
                @endphp
                <tr>
                    <td>
                        <div class="td-main">{{ $t->teacher_name }}</div>
                        <div class="td-sub">{{ $t->exams_count ?? 0 }} exam(s) uploaded</div>
                    </td>
                    <td>{{ $t->subjects_count ?? 0 }}</td>
                    <td>{{ $t->total_students ?? 0 }}</td>
                    <td>
                        <div class="rate-wrap">
                            <div class="rate-bar">
                                <div class="rate-fill {{ $fillClass }}" style="width:{{ $rate }}%"></div>
                            </div>
                            <span class="rate-label {{ $fillClass }}">{{ $rate }}%</span>
                        </div>
                    </td>
                    <td>
                        @if($isAtRisk)
                            <span class="risk-badge">At Risk</span>
                        @else
                            <span class="ok-badge">On Track</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="5">No teacher data available for this semester.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Right column --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Subjects at Risk --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Subjects at Risk</span>
                <span style="font-size:11px;color:var(--text-soft)">Below 60% pass</span>
            </div>
            <div class="risk-list">
                @forelse($subjectsAtRisk as $subject)
                <div class="risk-row">
                    <div>
                        <div class="risk-subject">{{ $subject->subject_code }}</div>
                        <div class="risk-teacher">{{ $subject->teacher_name }}</div>
                    </div>
                    <span class="risk-rate">{{ $subject->pass_rate }}%</span>
                </div>
                @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-soft)">
                    No subjects at risk. 🎉
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Quick Actions</span>
            </div>
            <div class="quick-grid">
                <a href="{{ route('admin.teachers.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/>
                    </svg>
                    Add Teacher
                </a>
                <a href="{{ route('admin.subjects.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        <line x1="12" y1="7" x2="12" y2="13"/><line x1="9" y1="10" x2="15" y2="10"/>
                    </svg>
                    Add Subject
                </a>
                <a href="{{ route('admin.departments.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <line x1="12" y1="14" x2="12" y2="20"/><line x1="9" y1="17" x2="15" y2="17"/>
                    </svg>
                    Add Department
                </a>
                <a href="{{ route('admin.school-years.create') }}" class="quick-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/>
                    </svg>
                    New School Year
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function filterBySemester(semId) {
        const url = new URL(window.location.href);
        if (semId === 'all') {
            url.searchParams.delete('semester_id');
        } else {
            url.searchParams.set('semester_id', semId);
        }
        window.location.href = url.toString();
    }
</script>
@endpush

@endsection