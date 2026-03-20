{{-- resources/views/assistant/dashboard.blade.php --}}
@extends('layouts.assistant')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px; }
    .stat-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:20px 22px; position:relative; overflow:hidden; animation:slideUp .4s ease both; }
    .stat-card:nth-child(1){animation-delay:.05s} .stat-card:nth-child(2){animation-delay:.10s} .stat-card:nth-child(3){animation-delay:.15s}
    @keyframes slideUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    .stat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:12px 12px 0 0; }
    .c-teal-card::before{background:var(--teal-light)} .c-red-card::before{background:var(--red)} .c-amber-card::before{background:var(--gold)}
    .stat-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; margin-bottom:14px; }
    .stat-icon svg { width:18px; height:18px; }
    .c-teal-card .stat-icon{background:var(--green-bg);color:var(--green)}
    .c-red-card .stat-icon{background:var(--red-bg);color:var(--red)}
    .c-amber-card .stat-icon{background:var(--amber-bg);color:var(--amber)}
    .stat-value { font-family:'DM Serif Display',serif; font-size:32px; line-height:1; color:var(--text-dark); margin-bottom:4px; }
    .stat-label { font-size:12px; color:var(--text-soft); }
    .bottom-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; }
    .card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; animation:slideUp .4s ease .2s both; }
    .card-header { padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
    .card-title { font-family:'DM Serif Display',serif; font-size:16px; color:var(--text-dark); }
    .card-action { font-size:12px; color:var(--teal-light); text-decoration:none; font-weight:500; }
    table { width:100%; border-collapse:collapse; }
    thead th { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.8px; color:var(--text-soft); padding:10px 22px; text-align:left; background:#faf8f5; border-bottom:1px solid var(--border); }
    tbody td { padding:11px 22px; font-size:13px; border-bottom:1px solid #f3efe8; color:var(--text-mid); }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#faf8f5; }
    .td-main { font-weight:500; color:var(--text-dark); }
    .td-sub { font-size:11px; color:var(--text-soft); margin-top:2px; }
    .badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-pass{background:var(--green-bg);color:var(--green)} .badge-fail{background:var(--red-bg);color:var(--red)}
    .badge-prelim{background:var(--amber-bg);color:var(--amber)} .badge-midterm{background:var(--blue-bg);color:var(--blue)} .badge-final{background:#f0ebfa;color:#534ab7}
    .teacher-list { padding:8px 0; }
    .teacher-row { display:flex; align-items:center; justify-content:space-between; padding:10px 22px; border-bottom:1px solid #f3efe8; }
    .teacher-row:last-child { border-bottom:none; }
    .teacher-name { font-size:13px; font-weight:500; color:var(--text-dark); }
    .teacher-meta { font-size:11px; color:var(--text-soft); margin-top:2px; }
    .fail-pill { font-size:10px; font-weight:600; background:var(--red-bg); color:var(--red); padding:2px 8px; border-radius:20px; }
    .ok-pill { font-size:10px; font-weight:600; background:var(--green-bg); color:var(--green); padding:2px 8px; border-radius:20px; }
    .upload-cta { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 24px; text-align:center; gap:12px; }
    .upload-cta svg { width:40px; height:40px; color:var(--border); }
    .upload-cta p { font-size:13px; color:var(--text-soft); }
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; border:none; cursor:pointer; transition:all .15s; }
    .btn-primary { background:var(--navy); color:var(--white); }
    .btn-primary:hover { background:#1e3050; }
</style>
@endpush

@section('content')

<div class="stats-grid">
    <div class="stat-card c-teal-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-value">{{ $totalTeachers }}</div>
        <div class="stat-label">Total teachers</div>
    </div>
    <div class="stat-card c-amber-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-value">{{ $totalStudents }}</div>
        <div class="stat-label">Total students</div>
    </div>
    <div class="stat-card c-red-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="stat-value">{{ $failingStudents }}</div>
        <div class="stat-label">Failing students</div>
    </div>
</div>

<div class="bottom-grid">
    {{-- Recent results --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent exam results</span>
            <a href="{{ route('assistant.subjects.index') }}" class="card-action">View all →</a>
        </div>
        @if($recentResults->count())
        <table>
            <thead><tr><th>Student</th><th>Teacher</th><th>Subject</th><th>Exam</th><th>%</th><th>Remark</th></tr></thead>
            <tbody>
                @foreach($recentResults as $result)
                <tr>
                    <td>
                        <div class="td-main">{{ $result->student->student_name }}</div>
                        <div class="td-sub">{{ $result->student->student_code }}</div>
                    </td>
                    <td style="font-size:12px">{{ $result->exam->teacherSubject->teacher->teacher_name }}</td>
                    <td>{{ $result->exam->teacherSubject->subject->subject_code }}</td>
                    <td><span class="badge badge-{{ $result->exam->exam_type }}">{{ ucfirst($result->exam->exam_type) }}</span></td>
                    <td>{{ $result->percentage }}%</td>
                    <td><span class="badge badge-{{ $result->remark }}">{{ ucfirst($result->remark) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="upload-cta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p>No exam results yet. Upload a Master List PDF to get started.</p>
            <a href="{{ route('assistant.upload.index') }}" class="btn btn-primary">Upload PDF</a>
        </div>
        @endif
    </div>

    {{-- Teachers overview --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Teachers overview</span>
            <a href="{{ route('assistant.interventions.index') }}" class="card-action">Full report →</a>
        </div>
        <div class="teacher-list">
            @forelse($teachers as $teacher)
            <div class="teacher-row">
                <div>
                    <div class="teacher-name">{{ $teacher->teacher_name }}</div>
                    <div class="teacher-meta">{{ $teacher->teacher_subjects_count }} subject(s)</div>
                </div>
                @if($teacher->failing_count > 0)
                    <span class="fail-pill">{{ $teacher->failing_count }} failing</span>
                @else
                    <span class="ok-pill">All passing</span>
                @endif
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-soft)">No teachers yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection