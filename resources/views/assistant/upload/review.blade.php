{{-- resources/views/assistant/upload/review.blade.php --}}
@extends('layouts.assistant')
@section('title', 'Review Extracted Results')
@section('page-title', 'Review Extracted Results')

@push('styles')
<style>
    .review-header { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:20px 24px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; animation:slideUp .3s ease both; }
    @keyframes slideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .review-meta { display:flex; gap:28px; flex-wrap:wrap; }
    .meta-item { display:flex; flex-direction:column; gap:3px; }
    .meta-label { font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:var(--text-soft); font-weight:600; }
    .meta-value { font-size:14px; font-weight:500; color:var(--text-dark); }
    .meta-value.teacher { color:var(--teal); }
    .summary-pills { display:flex; gap:10px; flex-wrap:wrap; }
    .pill { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    .pill-total{background:#f0ece3;color:var(--text-mid)} .pill-pass{background:var(--green-bg);color:var(--green)} .pill-fail{background:var(--red-bg);color:var(--red)} .pill-flag{background:var(--amber-bg);color:var(--amber)}
    .teacher-banner { display:flex; align-items:center; gap:10px; padding:12px 16px; background:#f0faf7; border:1px solid #9fe1cb; border-radius:8px; font-size:13px; color:var(--teal); margin-bottom:16px; }
    .teacher-banner svg { width:16px; height:16px; flex-shrink:0; }
    .teacher-banner strong { font-weight:600; }
    .card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; animation:slideUp .3s ease .1s both; }
    .card-header { padding:16px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
    .card-title { font-family:'DM Serif Display',serif; font-size:16px; color:var(--text-dark); }
    .card-sub { font-size:12px; color:var(--text-soft); }
    table { width:100%; border-collapse:collapse; }
    thead th { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.7px; color:var(--text-soft); padding:10px 16px; text-align:left; background:#faf8f5; border-bottom:1px solid var(--border); }
    tbody td { padding:0; border-bottom:1px solid #f3efe8; vertical-align:middle; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr.flagged { background:#fffbf2; }
    .td-inner { padding:8px 16px; font-size:13px; color:var(--text-mid); }
    .td-num { font-size:12px; color:var(--text-soft); text-align:center; }
    input.inline-edit { width:100%; padding:6px 10px; font-family:'DM Sans',sans-serif; font-size:13px; background:#fffef8; border:1.5px solid #f0c84a; border-radius:6px; color:var(--text-dark); outline:none; transition:border-color .2s; }
    input.inline-edit:focus { border-color:var(--amber); box-shadow:0 0 0 3px rgba(183,98,26,.1); }
    input.inline-edit.ok { background:#faf8f5; border-color:var(--border); }
    .flag-badge { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:600; background:var(--amber-bg); color:var(--amber); padding:2px 7px; border-radius:10px; }
    .badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .badge-pass{background:var(--green-bg);color:var(--green)} .badge-fail{background:var(--red-bg);color:var(--red)}
    .form-footer { margin-top:20px; display:flex; align-items:center; justify-content:space-between; padding:16px 0; }
    .btn { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; border:none; cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif; }
    .btn-primary { background:var(--navy); color:var(--white); }
    .btn-primary:hover { background:#1e3050; }
    .btn-secondary { background:transparent; color:var(--text-mid); border:1.5px solid var(--border); }
    .flag-info { display:flex; gap:10px; padding:12px 16px; background:var(--amber-bg); border:1px solid #f0c84a; border-radius:8px; font-size:12px; color:var(--amber); line-height:1.6; margin-bottom:16px; }
    .flag-info svg { flex-shrink:0; width:15px; height:15px; margin-top:1px; }
    .pct { font-weight:500; }
    .pct-fail { color:var(--red); }
    .pct-pass { color:var(--green); }

    /* ── Item Analysis Matrix ─────────────────────────────────────────── */
    .matrix-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; margin-bottom:20px; animation:slideUp .3s ease .05s both; }
    .matrix-meta { display:flex; gap:6px; flex-wrap:wrap; align-items:center; margin-top:6px; }
    .matrix-tag { font-size:11px; font-weight:500; padding:3px 9px; border-radius:12px; background:#f0ece3; color:var(--text-mid); }
    .matrix-wrap { overflow-x:auto; }
    .matrix-table { width:100%; border-collapse:collapse; min-width:700px; }
    .matrix-table th {
        font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
        color:var(--text-soft); padding:9px 12px; background:#faf8f5;
        border-bottom:1px solid var(--border); border-right:1px solid var(--border);
        text-align:center; white-space:nowrap;
    }
    .matrix-table th.th-left { text-align:left; min-width:160px; }
    .matrix-table th:last-child { border-right:none; }
    .matrix-table td {
        font-size:12px; color:var(--text-mid); padding:9px 12px;
        border-bottom:1px solid #f3efe8; border-right:1px solid #f3efe8;
        vertical-align:top; text-align:center;
    }
    .matrix-table td:last-child { border-right:none; }
    .matrix-table tbody tr:last-child td { border-bottom:none; }
    .diff-label { text-align:left !important; font-weight:600; color:var(--text-dark); white-space:nowrap; background:#fdfcfa; }
    .diff-sub { display:block; font-size:10px; font-weight:400; color:var(--text-soft); margin-top:2px; }
    .item-chips { display:flex; flex-wrap:wrap; gap:3px; justify-content:center; }
    .item-chip { display:inline-block; font-size:10px; font-weight:600; padding:2px 6px; border-radius:8px; line-height:1.6; }
    .chip-reject          { background:#fde8e8; color:#c0392b; }
    .chip-needs-revision  { background:#fff3cd; color:#856404; }
    .chip-acceptable      { background:#d4edda; color:#1a6e34; }
    .empty-cross { color:#d0cac0; font-size:16px; letter-spacing:3px; }
    .total-cell { font-weight:700; color:var(--text-dark); background:#faf8f5; font-size:13px; text-align:center !important; }
    .matrix-table tfoot td {
        font-size:11px; font-weight:700; color:var(--text-dark); background:#faf8f5;
        padding:9px 12px; border-top:2px solid var(--border);
        border-right:1px solid var(--border); text-align:center;
    }
    .matrix-table tfoot td.col-label { text-align:left; }
    .matrix-table tfoot td:last-child { border-right:none; }
    .sub-label { font-size:9px; font-weight:500; display:block; margin-top:2px; text-transform:uppercase; letter-spacing:.4px; }
    .sub-reject         { color:#c0392b; }
    .sub-needs-revision { color:#856404; }
    .sub-acceptable     { color:#1a6e34; }
    .matrix-legend { display:flex; gap:20px; flex-wrap:wrap; padding:11px 18px; border-top:1px solid var(--border); background:#fdfcfa; align-items:center; }
    .legend-item { display:flex; align-items:center; gap:6px; font-size:11px; color:var(--text-mid); }
    .legend-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
    .legend-dot.reject         { background:#c0392b; }
    .legend-dot.needs-revision { background:#856404; }
    .legend-dot.acceptable     { background:#1a6e34; }
    .legend-count              { font-weight:700; margin-left:2px; }
    .legend-count.reject       { color:#c0392b; }
    .legend-count.needs-revision { color:#856404; }
    .legend-count.acceptable   { color:#1a6e34; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('assistant.upload.store') }}">
@csrf

<input type="hidden" name="teacher_subject_id" value="{{ $context['teacher_subject_id'] }}">
<input type="hidden" name="exam_type"           value="{{ $context['exam_type'] }}">
<input type="hidden" name="item_matrix_path"    value="{{ $context['item_matrix_path'] ?? '' }}">

{{-- Review header --}}
<div class="review-header">
    <div class="review-meta">
        <div class="meta-item">
            <span class="meta-label">Teacher</span>
            <span class="meta-value teacher">{{ $context['teacher_name'] }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Subject</span>
            <span class="meta-value">{{ $context['subject_code'] }} — {{ $context['section'] }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Exam type</span>
            <span class="meta-value">{{ ucfirst($context['exam_type']) }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Semester</span>
            <span class="meta-value">{{ $context['semester'] }}</span>
        </div>
    </div>
    <div class="summary-pills">
        <span class="pill pill-total">{{ count($rows) }} students</span>
        <span class="pill pill-pass">{{ collect($rows)->where('remark','pass')->count() }} pass</span>
        <span class="pill pill-fail">{{ collect($rows)->where('remark','fail')->count() }} fail</span>
        @if(collect($rows)->where('flagged',true)->count())
            <span class="pill pill-flag">{{ collect($rows)->where('flagged',true)->count() }} flagged</span>
        @endif
    </div>
</div>

{{-- Teacher banner --}}
<div class="teacher-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span>You are uploading exam results on behalf of <strong>{{ $context['teacher_name'] }}</strong> for <strong>{{ $context['subject_code'] }} — {{ $context['section'] }}</strong>. Please confirm all information is correct before saving.</span>
</div>

@if(collect($rows)->where('flagged',true)->count())
<div class="flag-info">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <span>Some rows are missing a student name or code (highlighted in yellow). Please fill them in before saving — rows left blank will be skipped.</span>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- Item Analysis Matrix                                                   --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if(!empty($matrixData) && ($matrixData['total_items'] ?? 0) > 0)
@php
    $discCols  = \App\Services\ItemMatrixParser::DISCRIMINATION_COLS;
    $diffBands = \App\Services\ItemMatrixParser::DIFFICULTY_BANDS;

    $chipClass = function(string $col): string {
        if (in_array($col, ['<.00', '.00-.14']))    return 'chip-reject';
        if (in_array($col, ['.15-.24', '.25-.29'])) return 'chip-needs-revision';
        return 'chip-acceptable';
    };

    $subLabel = function(string $col): string {
        if (in_array($col, ['<.00', '.00-.14']))    return '<span class="sub-label sub-reject">Reject</span>';
        if (in_array($col, ['.15-.24', '.25-.29'])) return '<span class="sub-label sub-needs-revision">Revise</span>';
        return '<span class="sub-label sub-acceptable">Accept</span>';
    };
@endphp
<div class="matrix-card">
    <div class="card-header">
        <div>
            <div class="card-title">Item Analysis Matrix</div>
            <div class="matrix-meta">
                @if($matrixData['title'])  <span class="matrix-tag">{{ $matrixData['title'] }}</span>  @endif
                @if($matrixData['module']) <span class="matrix-tag">{{ $matrixData['module'] }}</span> @endif
                @if($matrixData['date'])   <span class="matrix-tag">{{ $matrixData['date'] }}</span>   @endif
            </div>
        </div>
        <div class="summary-pills">
            <span class="pill pill-total">{{ $matrixData['total_items'] }} items</span>
            @if(count($matrixData['legend']['reject'] ?? []) > 0)
                <span class="pill pill-fail">{{ count($matrixData['legend']['reject']) }} reject</span>
            @endif
            @if(count($matrixData['legend']['needs_revision'] ?? []) > 0)
                <span class="pill pill-flag">{{ count($matrixData['legend']['needs_revision']) }} revise</span>
            @endif
            @if(count($matrixData['legend']['acceptable'] ?? []) > 0)
                <span class="pill pill-pass">{{ count($matrixData['legend']['acceptable']) }} acceptable</span>
            @endif
        </div>
    </div>

    <div class="matrix-wrap">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th class="th-left">Difficulty</th>
                    @foreach($discCols as $col)
                        <th>
                            {{ $col }}
                            {!! $subLabel($col) !!}
                        </th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($diffBands as $band => $label)
                <tr>
                    <td class="diff-label">
                        {{ $band }}
                        <span class="diff-sub">{{ $label }}</span>
                    </td>
                    @foreach($discCols as $col)
                    @php $items = $matrixData['cells'][$band][$col] ?? []; @endphp
                    <td>
                        @if(count($items))
                            <div class="item-chips">
                                @foreach($items as $item)
                                    <span class="item-chip {{ $chipClass($col) }}">{{ $item }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="empty-cross">×</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="total-cell">{{ $matrixData['row_totals'][$band] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="col-label">Total</td>
                    @foreach($discCols as $col)
                        <td>{{ $matrixData['col_totals'][$col] ?? 0 }}</td>
                    @endforeach
                    <td>{{ $matrixData['total_items'] }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="matrix-legend">
        <div class="legend-item">
            <span class="legend-dot reject"></span>
            Reject (&lt;.00 – .00-.14):
            <span class="legend-count reject">{{ count($matrixData['legend']['reject'] ?? []) }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot needs-revision"></span>
            Needs Revision (.15-.24 – .25-.29):
            <span class="legend-count needs-revision">{{ count($matrixData['legend']['needs_revision'] ?? []) }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot acceptable"></span>
            Acceptable (.30-.44 – .45 and above):
            <span class="legend-count acceptable">{{ count($matrixData['legend']['acceptable'] ?? []) }}</span>
        </div>
    </div>
</div>
@endif
{{-- ══════════════════════════════════════════════════════════════════════ --}}

{{-- Student results table --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Extracted results — {{ count($rows) }} students</span>
        <span class="card-sub">Review and correct flagged rows before saving</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:40px;text-align:center">#</th>
                <th>Student name</th>
                <th>Student code</th>
                <th>Raw score [T]</th>
                <th>Percentage</th>
                <th>Remark</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
            <tr class="{{ $row['flagged'] ? 'flagged' : '' }}">
                <td><div class="td-inner td-num">{{ $row['row_number'] }}</div></td>
                <td>
                    <div class="td-inner" style="padding:6px 16px">
                        <input type="text"
                            name="students[{{ $i }}][student_name]"
                            value="{{ old("students.$i.student_name", $row['student_name']) }}"
                            class="inline-edit {{ $row['flagged'] ? '' : 'ok' }}"
                            placeholder="Enter student name">
                    </div>
                </td>
                <td>
                    <div class="td-inner" style="padding:6px 16px">
                        <input type="text"
                            name="students[{{ $i }}][student_code]"
                            value="{{ old("students.$i.student_code", $row['student_code']) }}"
                            class="inline-edit {{ $row['flagged'] ? '' : 'ok' }}"
                            placeholder="Enter code"
                            style="width:130px">
                    </div>
                </td>
                <input type="hidden" name="students[{{ $i }}][raw_score]"  value="{{ $row['raw_score'] }}">
                <input type="hidden" name="students[{{ $i }}][percentage]" value="{{ $row['percentage'] }}">
                <input type="hidden" name="students[{{ $i }}][remark]"     value="{{ $row['remark'] }}">
                <input type="hidden" name="students[{{ $i }}][row_number]" value="{{ $row['row_number'] }}">
                <td><div class="td-inner">{{ $row['raw_score'] }}</div></td>
                <td>
                    <div class="td-inner">
                        <span class="pct {{ $row['remark'] === 'fail' ? 'pct-fail' : 'pct-pass' }}">
                            {{ $row['percentage'] }}%
                        </span>
                    </div>
                </td>
                <td>
                    <div class="td-inner">
                        <span class="badge badge-{{ $row['remark'] }}">{{ ucfirst($row['remark']) }}</span>
                    </div>
                </td>
                <td>
                    <div class="td-inner">
                        @if($row['flagged'])
                            <span class="flag-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                                Needs info
                            </span>
                        @else
                            <span style="font-size:11px;color:var(--green)">✓ OK</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="form-footer">
    <a href="{{ route('assistant.upload.index') }}" class="btn btn-secondary">← Re-upload</a>
    <button type="submit" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save results
    </button>
</div>
</form>
@endsection