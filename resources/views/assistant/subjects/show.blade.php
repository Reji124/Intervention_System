@extends('layouts.assistant')
@section('title', $teacherSubject->subject->subject_code)
@section('page-title', $teacherSubject->subject->subject_name)

@push('styles')
<style>
    .subject-header { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:20px 24px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; }
    .sh-left h2 { font-family:'DM Serif Display',serif; font-size:22px; color:var(--text-dark); }
    .sh-meta { font-size:13px; color:var(--text-soft); margin-top:6px; display:flex; flex-wrap:wrap; gap:6px 0; }
    .sh-meta span { margin-right:16px; }
    .sh-meta .meta-course { font-weight:500; color:var(--text-mid); }
    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; border:none; cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif; }
    .btn-primary { background:var(--navy); color:var(--white); }
    .btn-primary:hover { background:#1e3050; }
    .btn-outline { background:transparent; color:var(--text-mid); border:1.5px solid var(--border); }
    .btn-outline:hover { border-color:var(--text-mid); }
    .exam-section { margin-bottom:24px; }
    .exam-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .exam-title { font-family:'DM Serif Display',serif; font-size:17px; color:var(--text-dark); display:flex; align-items:center; gap:10px; }
    .exam-pills { display:flex; gap:8px; }
    .pill { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .pill-pass { background:var(--green-bg); color:var(--green); }
    .pill-fail { background:var(--red-bg); color:var(--red); }
    .card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    table { width:100%; border-collapse:collapse; }
    thead th { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.7px; color:var(--text-soft); padding:10px 20px; text-align:left; background:#faf8f5; border-bottom:1px solid var(--border); }
    tbody td { padding:11px 20px; font-size:13px; border-bottom:1px solid #f3efe8; color:var(--text-mid); }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#faf8f5; }
    .td-name { font-weight:500; color:var(--text-dark); }
    .badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-pass { background:var(--green-bg); color:var(--green); }
    .badge-fail { background:var(--red-bg); color:var(--red); }
    .badge-prelim  { background:var(--amber-bg); color:var(--amber); }
    .badge-midterm { background:var(--blue-bg);  color:var(--blue); }
    .badge-final   { background:#f0ebfa; color:#534ab7; }
    .no-exam { text-align:center; padding:40px; color:var(--text-soft); font-size:13px; }
    .matrix-link { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--teal-light); text-decoration:none; }
    .matrix-link:hover { text-decoration:underline; }
    .pct-bar { display:flex; align-items:center; gap:8px; }
    .pct-track { flex:1; height:4px; background:var(--border); border-radius:4px; max-width:80px; }
    .pct-fill { height:100%; border-radius:4px; }
    .pct-pass-fill { background:var(--green); }
    .pct-fail-fill  { background:var(--red); }
</style>
@endpush

@section('content')

<div class="subject-header">
    <div class="sh-left">
        <h2>{{ $teacherSubject->subject->subject_code }} — {{ $teacherSubject->section }}</h2>
        <div class="sh-meta">
            <span>{{ $teacherSubject->subject->subject_name }}</span>
            <span class="meta-course">{{ $teacherSubject->subject->course->course_name }}</span>
            <span>{{ $teacherSubject->subject->department->department_name }}</span>
            <span>Year {{ $teacherSubject->subject->year_level }}</span>
            <span>{{ $teacherSubject->semester->semester_name }} Sem, S.Y. {{ $teacherSubject->semester->schoolYear->year_start }}–{{ $teacherSubject->semester->schoolYear->year_end }}</span>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('assistant.upload.index') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Upload results
        </a>
        <a href="{{ route('assistant.subjects.index') }}" class="btn btn-outline">← Back</a>
    </div>
</div>

@if($resultsByExam->isEmpty())
    <div class="card">
        <div class="no-exam">
            No exam results uploaded yet for this subject.
            <br><br>
            <a href="{{ route('assistant.upload.index') }}" class="btn btn-primary" style="display:inline-flex">
                Upload PDF
            </a>
        </div>
    </div>
@else
    @foreach(['prelim','midterm','final'] as $examType)
        @if(isset($resultsByExam[$examType]))
        @php $data = $resultsByExam[$examType]; @endphp
        <div class="exam-section">
            <div class="exam-header">
                <div class="exam-title">
                    <span class="badge badge-{{ $examType }}">{{ ucfirst($examType) }}</span>
                    Results                  
                </div>
                <div class="exam-pills">
                    <span class="pill pill-pass">{{ $data['pass'] }} pass</span>
                    <span class="pill pill-fail">{{ $data['fail'] }} fail</span>
                </div>
            </div>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Code</th>
                            <th>Raw score</th>
                            <th>Percentage</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['results'] as $result)
                        <tr>
                            <td><div class="td-name">{{ $result->student->student_name }}</div></td>
                            <td><div style="font-size:12px;color:var(--text-soft)">{{ $result->student->student_code }}</div></td>
                            <td>{{ $result->raw_score }}</td>
                            <td>
                                <div class="pct-bar">
                                    {{ $result->percentage }}%
                                    <div class="pct-track">
                                        <div class="pct-fill {{ $result->remark === 'pass' ? 'pct-pass-fill' : 'pct-fail-fill' }}"
                                             style="width:{{ min($result->percentage, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-{{ $result->remark }}">{{ ucfirst($result->remark) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endforeach
@endif

@endsection