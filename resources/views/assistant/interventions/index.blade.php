{{-- resources/views/assistant/interventions/index.blade.php --}}
@extends('layouts.assistant')
@section('title', 'Intervention Report')
@section('page-title', 'Intervention Report')

@push('styles')
<style>
/* ── Report bar ───────────────────────────────────────────────────────────── */
.report-bar {
    background: var(--navy);
    margin: -28px -32px 0;
    padding: 28px 32px 24px;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
}
.report-bar::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(29,158,117,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(29,158,117,.06) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}
.report-bar-inner {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.report-bar-left h2 {
    font-family: 'DM Serif Display', serif;
    font-size: 26px;
    color: #fff;
    margin-bottom: 5px;
}
.report-bar-left p {
    font-size: 13px;
    color: rgba(255,255,255,.5);
}
.btn-print {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    border: 1.5px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.7);
    background: rgba(255,255,255,.06);
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: all .15s;
    flex-shrink: 0;
}
.btn-print:hover { background: rgba(255,255,255,.12); color: #fff; }
.btn-print svg { width: 14px; height: 14px; }

/* ── Results ─────────────────────────────────────────────────────────────── */
.results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 8px;
}
.results-count { font-size: 13px; color: var(--text-soft); }
.results-count strong { color: var(--text-dark); }
.expand-all-btn {
    font-size: 12px;
    color: var(--teal-light, #1d9e75);
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    padding: 0;
}

/* ── Teacher block ────────────────────────────────────────────────────────── */
.teacher-block {
    background: var(--white, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 12px;
    transition: box-shadow .2s;
    animation: fadeIn .3s ease both;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
.teacher-block:hover { box-shadow: 0 2px 14px rgba(0,0,0,.07); }

.teacher-header {
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: background .15s;
    flex-wrap: wrap;
    gap: 12px;
    user-select: none;
}
.teacher-header:hover { background: #faf8f5; }

.teacher-info { display: flex; align-items: center; gap: 12px; }

.teacher-avatar {
    width: 44px;
    height: 44px;
    background: var(--navy);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DM Serif Display', serif;
    font-size: 15px;
    color: #5dcaa5;
    flex-shrink: 0;
}

.teacher-name-text {
    font-family: 'DM Serif Display', serif;
    font-size: 16px;
    color: var(--text-dark);
}
.teacher-sub-text {
    font-size: 12px;
    color: var(--text-soft);
    margin-top: 2px;
}

.teacher-right { display: flex; align-items: center; gap: 10px; }

.student-count-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 8px;
    background: #f0ece3;
    border: 1px solid #e0d9cf;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-mid, #6b7280);
}
.student-count-pill svg { width: 13px; height: 13px; flex-shrink: 0; }

.toggle-chevron {
    width: 20px;
    height: 20px;
    color: var(--text-soft);
    transition: transform .25s;
    flex-shrink: 0;
}
.toggle-chevron.open { transform: rotate(180deg); }

.teacher-body { border-top: 1px solid var(--border); display: none; }
.teacher-body.open { display: block; }

/* ── Subject block ────────────────────────────────────────────────────────── */
.subject-block { border-bottom: 1px solid #f3efe8; }
.subject-block:last-child { border-bottom: none; }

.subject-header {
    padding: 11px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    background: #fafafa;
    transition: background .15s;
    user-select: none;
}
.subject-header:hover { background: #f5f0e8; }

.subject-title-text {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}
.subject-pills { display: flex; gap: 6px; align-items: center; }

.subject-body { display: none; }
.subject-body.open { display: block; }

/* ── Status badges (matrix / upload indicators only) ──────────────────────── */
.indicator-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.indicator-badge.has-matrix   { background: var(--green-bg); color: var(--green); }
.indicator-badge.no-matrix    { background: #f0ece3; color: var(--text-soft); }
.indicator-badge.has-list     { background: #eef3ff; color: var(--blue, #2563eb); }
.indicator-badge svg { width: 9px; height: 9px; }

/* ── Count pill (small, neutral) ─────────────────────────────────────────── */
.count-pill-sm {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 8px;
    background: #f0ece3;
    border: 1px solid #e0d9cf;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-mid);
}
.count-pill-sm svg { width: 12px; height: 12px; flex-shrink: 0; }

/* ── Tabs ─────────────────────────────────────────────────────────────────── */
.subject-tabs {
    display: flex;
    border-bottom: 1px solid var(--border);
    background: #fafafa;
}
.subject-tab {
    padding: 10px 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-soft);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all .15s;
    user-select: none;
}
.subject-tab:hover { color: var(--text-dark); }
.subject-tab.active { color: var(--teal, #1d9e75); border-bottom-color: var(--teal, #1d9e75); }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Exam type badge ──────────────────────────────────────────────────────── */
.badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
.badge-prelim  { background: var(--amber-bg); color: var(--amber); }
.badge-midterm { background: var(--blue-bg);  color: var(--blue); }
.badge-final   { background: #f0ebfa;         color: #534ab7; }

/* ── Master list table ────────────────────────────────────────────────────── */
table.master-tbl { width: 100%; border-collapse: collapse; }
.master-tbl thead th {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: var(--text-soft);
    padding: 9px 16px;
    text-align: left;
    background: #f8f8f8;
    border-bottom: 1px solid var(--border);
}
.master-tbl tbody td {
    padding: 9px 16px;
    font-size: 13px;
    border-bottom: 1px solid #f3efe8;
    color: var(--text-mid);
    vertical-align: middle;
}
.master-tbl tbody tr:last-child td { border-bottom: none; }
.master-tbl tbody tr:hover td { background: #faf8f5; }
.td-name { font-weight: 500; color: var(--text-dark); }
.td-code { font-size: 11px; color: var(--text-soft); margin-top: 1px; }

/* ── Matrix ───────────────────────────────────────────────────────────────── */
.matrix-wrap-inner { overflow-x: auto; padding: 16px 22px; }
table.matrix-tbl { width: 100%; border-collapse: collapse; min-width: 560px; }
.matrix-tbl thead th {
    font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px;
    color: rgba(255,255,255,.75); padding: 8px 10px; text-align: center;
    background: var(--navy); border: 1px solid rgba(255,255,255,.08); white-space: nowrap;
}
.matrix-tbl thead th:first-child { text-align: left; min-width: 130px; }
.matrix-tbl thead .sub-row th { font-size: 9px; font-weight: 500; padding: 3px 10px 7px; background: var(--navy); border-top: none; }
.matrix-tbl tbody td { padding: 9px 10px; font-size: 11px; border: 1px solid var(--border); color: var(--text-mid); text-align: center; vertical-align: top; }
.matrix-tbl tbody td:first-child { text-align: left; font-weight: 600; padding-left: 14px; }
.matrix-tbl tbody tr:hover td { background: #faf8f5; }
.matrix-tbl .row-total { background: #f5f0e8 !important; font-weight: 700; color: var(--text-dark) !important; }
.matrix-tbl .totals-row td { background: var(--navy) !important; color: rgba(255,255,255,.9) !important; font-weight: 600; border-color: rgba(255,255,255,.1); }
.matrix-tbl .totals-row td:first-child { color: rgba(255,255,255,.6) !important; font-weight: 400; }
.item-chip-sm { display: inline-block; font-size: 10px; font-weight: 600; padding: 1px 5px; border-radius: 6px; margin: 1px; line-height: 1.5; }
.chip-reject { background: #fde8e8; color: #c0392b; }
.chip-needs-revision { background: #fff3cd; color: #856404; }
.chip-acceptable { background: #d4edda; color: #1a6e34; }
.diff-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; display: inline-block; }
.matrix-legend-row { display: flex; gap: 16px; flex-wrap: wrap; padding: 10px 22px; border-top: 1px solid var(--border); background: #fdfcfa; }
.legend-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text-mid); }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-dot.reject { background: #c0392b; }
.legend-dot.needs-revision { background: #856404; }
.legend-dot.acceptable { background: #1a6e34; }
.legend-count { font-weight: 700; margin-left: 2px; }
.legend-count.reject { color: #c0392b; }
.legend-count.needs-revision { color: #856404; }
.legend-count.acceptable { color: #1a6e34; }

/* ── Empty states ─────────────────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 60px 24px;
    background: var(--white, #fff);
    border: 1px solid var(--border);
    border-radius: 12px;
}
.empty-state h3 {
    font-family: 'DM Serif Display', serif;
    font-size: 22px;
    color: var(--text-mid);
    margin-bottom: 8px;
}
.empty-state p { font-size: 13px; color: var(--text-soft); }
.empty-row { padding: 20px 22px; font-size: 13px; color: var(--text-soft); font-style: italic; }

@media print {
    .report-bar { background: #fff !important; padding: 0 !important; margin: 0 0 24px !important; }
    .report-bar::before { display: none; }
    .report-bar-left h2 { color: #000 !important; }
    .report-bar-left p { color: #555 !important; }
    .btn-print, .expand-all-btn, .subject-tabs { display: none !important; }
    .teacher-body, .subject-body, .tab-panel { display: block !important; }
    .sidebar, .topbar { display: none !important; }
    .main { margin-left: 0 !important; }
}
</style>
@endpush

@section('content')

{{-- ── Report bar ── --}}
<div class="report-bar">
    <div class="report-bar-inner">
        <div class="report-bar-left">
            <h2>Intervention report</h2>
            <p>Monitor uploaded masterlists and item analysis matrices per teacher and subject</p>
        </div>
        <button class="btn-print" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
    </div>
</div>

{{-- ── Results ── --}}
@if($grouped->isEmpty())
    <div class="empty-state">
        <h3>No results uploaded yet</h3>
        <p>Upload exam results to see the intervention report.</p>
    </div>
@else

<div class="results-header">
    <p class="results-count">
        <strong>{{ $grouped->count() }}</strong> teacher(s) ·
        <strong>{{ $grouped->flatten(1)->sum('total_count') }}</strong> total results
    </p>
    <button class="expand-all-btn" id="expand-all-btn" onclick="expandAll()">Expand all</button>
</div>

@foreach($grouped as $teacherName => $subjectMap)
@php
    $tTotal = $subjectMap->sum('total_count');
    $inits  = collect(explode(' ', $teacherName))
                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                ->take(2)->implode('');
@endphp

<div class="teacher-block">

    {{-- ── Teacher header ── --}}
    <div class="teacher-header" onclick="toggleTeacher(this)">
        <div class="teacher-info">
            <div class="teacher-avatar">{{ $inits }}</div>
            <div>
                <div class="teacher-name-text">{{ $teacherName }}</div>
                <div class="teacher-sub-text">
                    {{ $subjectMap->count() }} {{ Str::plural('subject', $subjectMap->count()) }}
                </div>
            </div>
        </div>
        <div class="teacher-right">
            <div class="student-count-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                {{ $tTotal }} {{ Str::plural('student', $tTotal) }}
            </div>
            <svg class="toggle-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>

    {{-- ── Teacher body ── --}}
    <div class="teacher-body">

        @foreach($subjectMap as $subjectLabel => $subjectData)
        @php
            $sTotal    = $subjectData['total_count'];
            $examTypes = $subjectData['exam_types'];
        @endphp

        {{-- ── Subject block ── --}}
        <div class="subject-block" style="border-bottom: 2px solid var(--border)">

            <div class="subject-header" onclick="toggleSubject(this)">
                <div class="subject-title-text">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         style="width:13px;height:13px;color:var(--text-soft)">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                    {{ $subjectLabel }}
                </div>
                <div class="subject-pills">
                    {{-- Student count only, no pass/fail stats --}}
                    <span class="count-pill-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        {{ $sTotal }} {{ Str::plural('student', $sTotal) }}
                    </span>
                    <svg class="sub-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         style="width:14px;height:14px;color:var(--text-soft);transition:transform .2s">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
            </div>

            {{-- ── Subject body: exam types ── --}}
            <div class="subject-body">

                @foreach($examTypes as $examType => $examData)
                @php
                    $exam       = $examData['exam'];
                    $hasMatrix  = !empty($exam?->item_matrix_data);
                    $matrix     = $exam?->item_matrix_data ?? [];
                    $discCols   = $matrix['disc_columns']  ?? [];
                    $matrixRows = $matrix['rows']          ?? [];
                    $colTotals  = $matrix['column_totals'] ?? [];
                    $grandTotal = $matrix['grand_total']   ?? 0;
                    $legend     = $matrix['legend']        ?? [];
                    $diffColors = ['81-100%'=>'#27ae60','61-80%'=>'#2ecc71','41-60%'=>'#f39c12','21-40%'=>'#e67e22','0-20%'=>'#e74c3c'];
                    $chipClass  = function(string $col): string {
                        if (in_array($col, ['<.00', '.00-.14'])) return 'chip-reject';
                        if (in_array($col, ['.15-.24', '.25-.29'])) return 'chip-needs-revision';
                        return 'chip-acceptable';
                    };
                    $tabId   = 'tab-' . md5($teacherName . $subjectLabel . $examType);
                    $etTotal = $examData['total_count'];
                @endphp

                {{-- ── Exam-type sub-block ── --}}
                <div class="subject-block" style="background:#fdfcfa">

                    <div class="subject-header" style="padding-left:38px" onclick="toggleSubject(this)">
                        <div class="subject-title-text" style="gap:10px">
                            <span class="badge badge-{{ strtolower($examType) }}"
                                  style="padding:2px 10px;font-size:11px;font-weight:700;letter-spacing:.4px">
                                {{ ucfirst($examType) }}
                            </span>
                        </div>
                        <div class="subject-pills">
                            {{-- Student count only ── --}}
                            <span class="count-pill-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                {{ $etTotal }} {{ Str::plural('student', $etTotal) }}
                            </span>
                            {{-- Upload status indicators ── --}}
                            @if($examData['all_results']->count())
                            <span class="indicator-badge has-list">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Masterlist
                            </span>
                            @else
                            <span class="indicator-badge no-matrix">No masterlist</span>
                            @endif
                            @if($hasMatrix)
                            <span class="indicator-badge has-matrix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Matrix
                            </span>
                            @else
                            <span class="indicator-badge no-matrix">No matrix</span>
                            @endif
                            <svg class="sub-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 style="width:14px;height:14px;color:var(--text-soft);transition:transform .2s">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </div>
                    </div>

                    <div class="subject-body">

                        {{-- Tabs ── --}}
                        <div class="subject-tabs">
                            <div class="subject-tab active" onclick="switchTab(this, '{{ $tabId }}-students')">
                                Students ({{ $etTotal }})
                            </div>
                            @if($hasMatrix)
                            <div class="subject-tab" onclick="switchTab(this, '{{ $tabId }}-matrix')">
                                Item analysis matrix
                            </div>
                            @endif
                        </div>

                        {{-- ── Students tab ── --}}
                        <div id="{{ $tabId }}-students" class="tab-panel active">
                            @if($examData['all_results']->count())
                            <table class="master-tbl">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Raw score</th>
                                        <th>Percentage</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examData['all_results']->sortBy('student.student_name') as $i => $result)
                                    @if(!$result->student) @continue @endif
                                    <tr>
                                        <td style="color:var(--text-soft);font-size:11px">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="td-name">{{ $result->student->student_name }}</div>
                                            <div class="td-code">{{ $result->student->student_code }}</div>
                                        </td>
                                        <td>{{ $result->raw_score }}</td>
                                        <td>{{ $result->percentage }}%</td>
                                        <td>
                                            <span style="
                                                display:inline-block;font-size:10px;font-weight:600;
                                                padding:2px 8px;border-radius:20px;
                                                background:{{ $result->remark === 'pass' ? 'var(--green-bg)' : 'var(--red-bg)' }};
                                                color:{{ $result->remark === 'pass' ? 'var(--green)' : 'var(--red)' }};
                                            ">
                                                {{ ucfirst($result->remark) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="empty-row">No results recorded for this exam type yet.</div>
                            @endif
                        </div>{{-- /students tab --}}

                        {{-- ── Matrix tab ── --}}
                        @if($hasMatrix)
                        <div id="{{ $tabId }}-matrix" class="tab-panel">
                            <div class="matrix-wrap-inner">
                                <table class="matrix-tbl">
                                    <thead>
                                        <tr>
                                            <th>Difficulty</th>
                                            @foreach($discCols as $col)<th>{{ $col }}</th>@endforeach
                                            <th>Total</th>
                                        </tr>
                                        <tr class="sub-row">
                                            <th></th>
                                            @foreach($discCols as $col)
                                            <th>
                                                @if(in_array($col, ['<.00','.00-.14']))
                                                    <span style="color:#f09595">Reject</span>
                                                @elseif(in_array($col, ['.15-.24','.25-.29']))
                                                    <span style="color:#e8b45a">Revise</span>
                                                @else
                                                    <span style="color:#9fe1cb">Accept</span>
                                                @endif
                                            </th>
                                            @endforeach
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($matrixRows as $row)
                                        <tr>
                                            <td>
                                                <span class="diff-dot" style="background:{{ $diffColors[$row['difficulty']] ?? '#888' }}"></span>
                                                {{ $row['difficulty'] }}
                                                <span style="font-size:10px;color:var(--text-soft);font-weight:400;margin-left:2px">{{ $row['label'] ?? '' }}</span>
                                            </td>
                                            @foreach($discCols as $col)
                                            <td>
                                                @if(!empty($row['columns'][$col]))
                                                    <div style="display:flex;flex-wrap:wrap;gap:2px;justify-content:center">
                                                        @foreach($row['columns'][$col] as $item)
                                                        <span class="item-chip-sm {{ $chipClass($col) }}">{{ $item }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span style="color:var(--border);font-size:14px">×</span>
                                                @endif
                                            </td>
                                            @endforeach
                                            <td class="row-total">{{ $row['total'] ?? 0 }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="totals-row">
                                            <td>Total</td>
                                            @foreach($discCols as $col)<td>{{ $colTotals[$col] ?? 0 }}</td>@endforeach
                                            <td>{{ $grandTotal }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="matrix-legend-row">
                                <div class="legend-item"><span class="legend-dot reject"></span>Reject: <span class="legend-count reject">{{ count($legend['reject'] ?? []) }}</span></div>
                                <div class="legend-item"><span class="legend-dot needs-revision"></span>Needs Revision: <span class="legend-count needs-revision">{{ count($legend['needs_revision'] ?? []) }}</span></div>
                                <div class="legend-item"><span class="legend-dot acceptable"></span>Acceptable: <span class="legend-count acceptable">{{ count($legend['acceptable'] ?? []) }}</span></div>
                            </div>
                        </div>{{-- /matrix tab --}}
                        @endif

                    </div>{{-- /subject-body (exam type) --}}
                </div>{{-- /subject-block (exam type) --}}

                @endforeach {{-- exam_types --}}

            </div>{{-- /subject-body (subject) --}}
        </div>{{-- /subject-block (subject) --}}

        @endforeach {{-- subjectMap --}}

    </div>{{-- /teacher-body --}}
</div>{{-- /teacher-block --}}

@endforeach {{-- grouped --}}
@endif

@endsection

@push('scripts')
<script>
// ── Accordion helpers ─────────────────────────────────────────────────────
function toggleTeacher(header) {
    const body = header.nextElementSibling;
    const chev = header.querySelector('.toggle-chevron');
    body.classList.toggle('open');
    chev.classList.toggle('open');
}
function toggleSubject(header) {
    const body = header.nextElementSibling;
    const chev = header.querySelector('.sub-chevron');
    body.classList.toggle('open');
    chev.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : '';
}
function switchTab(tab, panelId) {
    const container = tab.closest('.subject-body');
    container.querySelectorAll('.subject-tab').forEach(t => t.classList.remove('active'));
    container.querySelectorAll('.tab-panel').forEach(p  => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById(panelId).classList.add('active');
}
function expandAll() {
    const btn     = document.getElementById('expand-all-btn');
    const bodies  = document.querySelectorAll('.teacher-body');
    const chevs   = document.querySelectorAll('.toggle-chevron');
    const anyOpen = document.querySelector('.teacher-body.open');
    bodies.forEach(b => b.classList.toggle('open', !anyOpen));
    chevs.forEach(c  => c.classList.toggle('open', !anyOpen));
    btn.textContent = anyOpen ? 'Expand all' : 'Collapse all';
}
</script>
@endpush