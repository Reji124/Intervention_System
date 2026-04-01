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
    .c-teal-card::before{background:var(--teal-light)} .c-amber-card::before{background:var(--gold)} .c-red-card::before{background:var(--red)}
    .stat-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; margin-bottom:14px; }
    .stat-icon svg { width:18px; height:18px; }
    .c-teal-card .stat-icon{background:var(--green-bg);color:var(--green)}
    .c-amber-card .stat-icon{background:var(--amber-bg);color:var(--amber)}
    .c-red-card .stat-icon{background:var(--red-bg);color:var(--red)}
    .stat-value { font-family:'DM Serif Display',serif; font-size:32px; line-height:1; color:var(--text-dark); margin-bottom:4px; }
    .stat-label { font-size:12px; color:var(--text-soft); }

    .bottom-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; }

    /* Semester tabs */
    .card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; animation:slideUp .4s ease .2s both; }
    .card-header { padding:18px 22px 0; border-bottom:1px solid var(--border); }
    .card-header-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
    .card-title { font-family:'DM Serif Display',serif; font-size:16px; color:var(--text-dark); }
    .card-action { font-size:12px; color:var(--teal-light); text-decoration:none; font-weight:500; }
    .sem-tabs { display:flex; gap:0; }
    .sem-tab { font-size:12px; font-weight:600; padding:8px 16px; border:none; background:transparent; cursor:pointer; color:var(--text-soft); border-bottom:2px solid transparent; margin-bottom:-1px; transition:all .15s; }
    .sem-tab.active { color:var(--teal-light); border-bottom-color:var(--teal-light); }
    .sem-tab:hover:not(.active) { color:var(--text-mid); }
    .sem-panel { display:none; }
    .sem-panel.active { display:block; }

    table { width:100%; border-collapse:collapse; }
    thead th { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.8px; color:var(--text-soft); padding:10px 22px; text-align:left; background:#faf8f5; border-bottom:1px solid var(--border); }
    tbody td { padding:11px 22px; font-size:13px; border-bottom:1px solid #f3efe8; color:var(--text-mid); }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#faf8f5; }
    .td-main { font-weight:500; color:var(--text-dark); }
    .td-sub { font-size:11px; color:var(--text-soft); margin-top:2px; }
    .badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-prelim{background:var(--amber-bg);color:var(--amber)}
    .badge-midterm{background:var(--blue-bg);color:var(--blue)}
    .badge-final{background:#f0ebfa;color:#534ab7}
    .timestamp { font-size:11px; color:var(--text-soft); }

    /* Empty state */
    .empty-state { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 24px; text-align:center; gap:12px; }
    .empty-state svg { width:40px; height:40px; color:var(--border); }
    .empty-state p { font-size:13px; color:var(--text-soft); }
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; border:none; cursor:pointer; transition:all .15s; }
    .btn-primary { background:var(--navy); color:var(--white); }
    .btn-primary:hover { background:#1e3050; }

    /* Teacher overview */
    .teacher-list { padding:8px 0; }
    .teacher-row { display:flex; align-items:center; justify-content:space-between; padding:10px 22px; border-bottom:1px solid #f3efe8; }
    .teacher-row:last-child { border-bottom:none; }
    .teacher-name { font-size:13px; font-weight:500; color:var(--text-dark); }
    .teacher-meta { font-size:11px; color:var(--text-soft); margin-top:2px; }
    .count-pill { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .count-pill-none { background:var(--red-bg); color:var(--red); }
    .count-pill-some { background:var(--amber-bg); color:var(--amber); }
    .count-pill-good { background:var(--green-bg); color:var(--green); }
    .teacher-card-header { padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
</style>
@endpush

@section('content')

{{-- ── Stats chips ── --}}
<div class="stats-grid">

    {{-- Total Exams Uploaded --}}
    <div class="stat-card c-teal-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalExamsUploaded }}</div>
        <div class="stat-label">Total exams uploaded</div>
    </div>

    {{-- Exams Pending Upload --}}
    <div class="stat-card c-red-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="stat-value">{{ $examsPendingUpload }}</div>
        <div class="stat-label">Exams pending upload</div>
    </div>

    {{-- Total Teachers --}}
    <div class="stat-card c-amber-card">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-value">{{ $totalTeachers }}</div>
        <div class="stat-label">Total teachers</div>
    </div>

</div>

{{-- ── Bottom grid ── --}}
<div class="bottom-grid">

    {{-- Recent Exams Uploaded (tabbed by semester) --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-top">
                <span class="card-title">Recent exams uploaded</span>
                <a href="{{ route('assistant.upload.index') }}" class="card-action">Upload exam →</a>
            </div>
            <div class="sem-tabs">
                <button class="sem-tab active" onclick="switchTab(event,'sem-1st')">1st Semester</button>
                <button class="sem-tab"        onclick="switchTab(event,'sem-2nd')">2nd Semester</button>
                <button class="sem-tab"        onclick="switchTab(event,'sem-summer')">Summer</button>
            </div>
        </div>

        {{-- 1st Semester --}}
        <div id="sem-1st" class="sem-panel active">
            @if(isset($recentExams['1st']) && $recentExams['1st']->count())
            <table>
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Exam Type</th>
                        <th>Uploaded By</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentExams['1st'] as $exam)
                    <tr>
                        <td class="td-main">{{ $exam->teacherSubject->teacher->teacher_name }}</td>
                        <td>{{ $exam->teacherSubject->subject->subject_code }}</td>
                        <td>
                            <div class="td-main">{{ $exam->teacherSubject->subject->subject_name }}</div>
                        </td>
                        <td><span class="badge badge-{{ $exam->exam_type }}">{{ ucfirst($exam->exam_type) }}</span></td>
                        <td>{{ $exam->uploadedBy->name ?? '—' }}</td>
                        <td class="timestamp">{{ $exam->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>No exams uploaded for 1st Semester yet.</p>
                <a href="{{ route('assistant.upload.index') }}" class="btn btn-primary">Upload PDF</a>
            </div>
            @endif
        </div>

        {{-- 2nd Semester --}}
        <div id="sem-2nd" class="sem-panel">
            @if(isset($recentExams['2nd']) && $recentExams['2nd']->count())
            <table>
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Exam Type</th>
                        <th>Uploaded By</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentExams['2nd'] as $exam)
                    <tr>
                        <td class="td-main">{{ $exam->teacherSubject->teacher->teacher_name }}</td>
                        <td>{{ $exam->teacherSubject->subject->subject_code }}</td>
                        <td>
                            <div class="td-main">{{ $exam->teacherSubject->subject->subject_name }}</div>
                        </td>
                        <td><span class="badge badge-{{ $exam->exam_type }}">{{ ucfirst($exam->exam_type) }}</span></td>
                        <td>{{ $exam->uploadedBy->name ?? '—' }}</td>
                        <td class="timestamp">{{ $exam->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>No exams uploaded for 2nd Semester yet.</p>
                <a href="{{ route('assistant.upload.index') }}" class="btn btn-primary">Upload PDF</a>
            </div>
            @endif
        </div>

        {{-- Summer --}}
        <div id="sem-summer" class="sem-panel">
            @if(isset($recentExams['summer']) && $recentExams['summer']->count())
            <table>
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Exam Type</th>
                        <th>Uploaded By</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentExams['summer'] as $exam)
                    <tr>
                        <td class="td-main">{{ $exam->teacherSubject->teacher->teacher_name }}</td>
                        <td>{{ $exam->teacherSubject->subject->subject_code }}</td>
                        <td>
                            <div class="td-main">{{ $exam->teacherSubject->subject->subject_name }}</div>
                        </td>
                        <td><span class="badge badge-{{ $exam->exam_type }}">{{ ucfirst($exam->exam_type) }}</span></td>
                        <td>{{ $exam->uploadedBy->name ?? '—' }}</td>
                        <td class="timestamp">{{ $exam->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>No exams uploaded for Summer yet.</p>
                <a href="{{ route('assistant.upload.index') }}" class="btn btn-primary">Upload PDF</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Teachers overview — exam upload count --}}
    <div class="card">
        <div class="teacher-card-header">
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
                @php $count = $teacher->exams_uploaded_count ?? 0; @endphp
                @if($count === 0)
                    <span class="count-pill count-pill-none">No exams</span>
                @elseif($count < 3)
                    <span class="count-pill count-pill-some">{{ $count }} exam(s)</span>
                @else
                    <span class="count-pill count-pill-good">{{ $count }} exams</span>
                @endif
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-soft)">No teachers yet.</div>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
    function switchTab(e, panelId) {
        // Deactivate all tabs and panels
        document.querySelectorAll('.sem-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.sem-panel').forEach(p => p.classList.remove('active'));
        // Activate clicked
        e.currentTarget.classList.add('active');
        document.getElementById(panelId).classList.add('active');
    }
</script>
@endpush

@endsection