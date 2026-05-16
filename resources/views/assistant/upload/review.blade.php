{{-- resources/views/assistant/upload/review.blade.php --}}
@extends('layouts.assistant')
@section('title', 'Review Extracted Results')
@section('page-title', 'Review Extracted Results')

@push('styles')
<style>
    /* ── Base ──────────────────────────────────────────────────────────── */
    @keyframes slideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

    /* ── Review header ─────────────────────────────────────────────────── */
    .review-header{background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;animation:slideUp .3s ease both}
    .review-meta{display:flex;gap:28px;flex-wrap:wrap}
    .meta-item{display:flex;flex-direction:column;gap:3px}
    .meta-label{font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);font-weight:600}
    .meta-value{font-size:14px;font-weight:500;color:var(--text-dark)}
    .meta-value.teacher{color:var(--teal)}
    .summary-pills{display:flex;gap:10px;flex-wrap:wrap}
    .pill{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:500}
    .pill-total{background:#f0ece3;color:var(--text-mid)}
    .pill-pass{background:var(--green-bg);color:var(--green)}
    .pill-fail{background:var(--red-bg);color:var(--red)}
    .pill-flag{background:var(--amber-bg);color:var(--amber)}

    /* ── Banners ───────────────────────────────────────────────────────── */
    .teacher-banner{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f0faf7;border:1px solid #9fe1cb;border-radius:8px;font-size:13px;color:var(--teal);margin-bottom:16px}
    .teacher-banner svg{width:16px;height:16px;flex-shrink:0}
    .teacher-banner strong{font-weight:600}
    .flag-info{display:flex;gap:10px;padding:12px 16px;background:var(--amber-bg);border:1px solid #f0c84a;border-radius:8px;font-size:12px;color:var(--amber);line-height:1.6;margin-bottom:16px}
    .flag-info svg{flex-shrink:0;width:15px;height:15px;margin-top:1px}

    /* ── Student results card ──────────────────────────────────────────── */
    .card{background:var(--card-bg);border:1px solid var(--border);border-radius:12px;overflow:hidden;animation:slideUp .3s ease .1s both}
    .card-header{padding:16px 22px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
    .card-title{font-family:'DM Serif Display',serif;font-size:16px;color:var(--text-dark)}
    .card-sub{font-size:12px;color:var(--text-soft)}
    table{width:100%;border-collapse:collapse}
    thead th{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft);padding:10px 16px;text-align:left;background:#faf8f5;border-bottom:1px solid var(--border)}
    tbody td{padding:0;border-bottom:1px solid #f3efe8;vertical-align:middle}
    tbody tr:last-child td{border-bottom:none}
    tbody tr.flagged{background:#fffbf2}
    .td-inner{padding:8px 16px;font-size:13px;color:var(--text-mid)}
    .td-num{font-size:12px;color:var(--text-soft);text-align:center}
    input.inline-edit{width:100%;padding:6px 10px;font-family:'DM Sans',sans-serif;font-size:13px;background:#fffef8;border:1.5px solid #f0c84a;border-radius:6px;color:var(--text-dark);outline:none;transition:border-color .2s}
    input.inline-edit:focus{border-color:var(--amber);box-shadow:0 0 0 3px rgba(183,98,26,.1)}
    input.inline-edit.ok{background:#faf8f5;border-color:var(--border)}
    .flag-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;background:var(--amber-bg);color:var(--amber);padding:2px 7px;border-radius:10px}
    .badge{display:inline-block;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px}
    .badge-pass{background:var(--green-bg);color:var(--green)}
    .badge-fail{background:var(--red-bg);color:var(--red)}
    .badge-mismatch{background:var(--amber-bg);color:var(--amber)}
    .db-name-hint{font-size:11px;color:var(--text-soft);margin-top:3px}
    .db-name-hint strong{color:var(--amber);font-weight:600}
    .pct{font-weight:500}
    .pct-fail{color:var(--red)}
    .pct-pass{color:var(--green)}

    /* ── Form footer ───────────────────────────────────────────────────── */
    .form-footer{margin-top:20px;display:flex;align-items:center;justify-content:space-between;padding:16px 0}
    .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;border:none;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif}
    .btn-primary{background:var(--navy);color:var(--white)}
    .btn-primary:hover{background:#1e3050}
    .btn-secondary{background:transparent;color:var(--text-mid);border:1.5px solid var(--border)}

    /* ══════════════════════════════════════════════════════════════════════
       Item Analysis Matrix — complete restyle
    ══════════════════════════════════════════════════════════════════════ */
    .matrix-card{background:var(--card-bg);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:20px;animation:slideUp .3s ease .05s both;position:relative}

    /* stat bar */
    .matrix-stat-bar{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px 20px;border-bottom:1px solid var(--border);background:#faf8f5}
    .matrix-stat-item{background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:10px 14px}
    .matrix-stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-soft);font-weight:600;margin-bottom:4px}
    .matrix-stat-val{font-size:28px;font-weight:600;line-height:1}
    .matrix-stat-val.sv-total{color:var(--text-dark)}
    .matrix-stat-val.sv-accept{color:#1a6e34}
    .matrix-stat-val.sv-revise{color:#856404}
    .matrix-stat-val.sv-reject{color:#c0392b}
    .matrix-stat-delta{font-size:11px;margin-top:4px;color:var(--text-soft)}
    .matrix-stat-delta.delta-add{color:#1a6e34;font-weight:500}
    .matrix-stat-delta.delta-rem{color:#c0392b;font-weight:500}

    /* edit toolbar */
    .matrix-edit-toolbar{padding:11px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;transition:background .2s}
    .matrix-edit-toolbar.editing{background:#fffbf2;border-bottom-color:#f0c84a}
    .matrix-edit-hint{font-size:12px;color:var(--text-soft)}
    .matrix-edit-hint.active{color:#856404;font-weight:500}
    .matrix-btn-group{display:flex;gap:8px}
    .mbtn{font-family:'DM Sans',sans-serif;cursor:pointer;border-radius:7px;font-size:12px;font-weight:500;padding:6px 14px;border:1.5px solid var(--border);background:var(--card-bg);color:var(--text-mid);transition:all .15s}
    .mbtn:hover{background:#f0ece3}
    .mbtn-edit{border-color:#f0c84a;color:#856404}
    .mbtn-edit:hover{background:var(--amber-bg)}
    .mbtn-confirm{background:#1d9e75;border-color:#1d9e75;color:#fff}
    .mbtn-confirm:hover{background:#0f6e56}
    .mbtn-cancel{color:var(--text-soft)}

    /* matrix table */
    .matrix-wrap{overflow-x:auto}
    .matrix-table{width:100%;border-collapse:collapse;min-width:760px}
    .matrix-table thead th{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-soft);padding:9px 10px;background:#faf8f5;border-bottom:1px solid var(--border);border-right:1px solid var(--border);text-align:center;white-space:nowrap}
    .matrix-table thead th.mth-left{text-align:left;min-width:155px}
    .matrix-table thead th:last-child{border-right:none}
    .m-sub{display:block;font-size:9px;font-weight:400;margin-top:2px;letter-spacing:.3px}
    .m-sub-r{color:#c0392b}.m-sub-n{color:#856404}.m-sub-a{color:#1a6e34}

    .matrix-table tbody td{font-size:12px;color:var(--text-mid);padding:8px 10px;border-bottom:1px solid #f3efe8;border-right:1px solid #f3efe8;vertical-align:top;text-align:center}
    .matrix-table tbody td:last-child{border-right:none}
    .matrix-table tbody tr:last-child td{border-bottom:none}
    .matrix-table .diff-label{text-align:left !important;font-weight:600;color:var(--text-dark);font-size:13px;background:#fdfcfa}
    .diff-sub-label{display:block;font-size:10px;font-weight:400;color:var(--text-soft);margin-top:2px}

    /* chips */
    .item-chips{display:flex;flex-wrap:wrap;gap:3px;justify-content:center;min-height:22px}
    .item-chip{display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;padding:2px 6px;border-radius:6px;line-height:1.6}
    .chip-r{background:#fde8e8;color:#c0392b}
    .chip-n{background:#fff3cd;color:#856404}
    .chip-a{background:#d4edda;color:#1a6e34}
    .chip-del{font-size:9px;opacity:.65;cursor:pointer;padding:0 1px;border-radius:2px;line-height:1}
    .chip-del:hover{opacity:1;background:rgba(0,0,0,.12)}
    .empty-cross{color:#d0cac0;font-size:15px}

    /* inline add input (edit mode) */
    .cell-add-row{display:flex;gap:4px;margin-top:5px;align-items:center;justify-content:center}
    .cell-num-input{font-family:'DM Sans',sans-serif;font-size:11px;width:46px;padding:3px 5px;border-radius:5px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-dark);text-align:center;outline:none}
    .cell-num-input:focus{border-color:var(--amber);box-shadow:0 0 0 2px rgba(183,98,26,.1)}
    .cell-add-go{font-family:'DM Sans',sans-serif;font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #9fe1cb;background:#e1f5ee;color:#0f6e56;cursor:pointer;font-weight:500}
    .cell-add-go:hover{background:#9fe1cb}

    /* totals */
    .total-cell{font-weight:700;color:var(--text-dark);background:#faf8f5;font-size:13px;text-align:center !important}
    .matrix-table tfoot td{font-size:11px;font-weight:700;color:var(--text-dark);background:#faf8f5;padding:9px 10px;border-top:2px solid var(--border);border-right:1px solid var(--border);text-align:center}
    .matrix-table tfoot td:last-child{border-right:none}
    .matrix-table tfoot td.mft-label{text-align:left}

    /* legend */
    .matrix-legend{display:flex;gap:20px;flex-wrap:wrap;padding:11px 18px;border-top:1px solid var(--border);background:#fdfcfa;align-items:center}
    .legend-item{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text-mid)}
    .legend-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
    .ld-r{background:#c0392b}.ld-n{background:#856404}.ld-a{background:#1a6e34}
    .legend-count{font-weight:700;margin-left:2px}
    .lc-r{color:#c0392b}.lc-n{color:#856404}.lc-a{color:#1a6e34}

    /* confirm overlay — uses normal flow container to avoid position:fixed */
    .confirm-overlay{display:none;position:absolute;inset:0;background:rgba(0,0,0,.45);z-index:20;border-radius:12px;align-items:center;justify-content:center}
    .confirm-overlay.show{display:flex}
    .confirm-box{background:var(--card-bg);border-radius:12px;border:1px solid var(--border);padding:24px 28px;max-width:420px;width:90%}
    .confirm-title{font-family:'DM Serif Display',serif;font-size:16px;color:var(--text-dark);margin-bottom:6px}
    .confirm-sub{font-size:13px;color:var(--text-soft);margin-bottom:14px;line-height:1.6}
    .confirm-diff{background:#faf8f5;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:var(--text-mid);line-height:1.9;border:1px solid var(--border)}
    .diff-add{color:#1a6e34;font-weight:600}
    .diff-rem{color:#c0392b;font-weight:600}
    .confirm-btns{display:flex;gap:8px;justify-content:flex-end}
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('assistant.upload.store') }}">
@csrf

<input type="hidden" name="teacher_subject_id" value="{{ $context['teacher_subject_id'] }}">
<input type="hidden" name="exam_type"           value="{{ $context['exam_type'] }}">
<input type="hidden" name="item_matrix_path"    value="{{ $context['item_matrix_path'] ?? '' }}">
<input type="hidden" name="grading_method" value="{{ $context['grading_method'] }}">

{{-- This hidden field will carry the (possibly edited) matrix JSON to the store action --}}
<input type="hidden" name="item_matrix_edited_json" id="item_matrix_edited_json"
    value="{{ !empty($matrixData) ? json_encode($matrixData, JSON_HEX_QUOT | JSON_HEX_TAG) : '' }}">

@php
    $totalRows     = count($rows);
    $passCount     = collect($rows)->where('remark', 'pass')->count();
    $failCount     = collect($rows)->where('remark', 'fail')->count();
    $flaggedCount  = collect($rows)->where('flagged', true)->count();
    $mismatchCount = collect($rows)->where('mismatch', true)->count();
@endphp

{{-- ── Review header ──────────────────────────────────────────────────── --}}
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
        <div class="meta-item">
            <span class="meta-label">Grading Method</span>
            <span class="meta-value">
                {{ \App\Models\Exam::gradingMethodLabel($context['grading_method'] ?? null) }}
            </span>
        </div>
    </div>
    <div class="summary-pills">
        <span class="pill pill-total">{{ $totalRows }} students</span>
        <span class="pill pill-pass">{{ $passCount }} pass</span>
        <span class="pill pill-fail">{{ $failCount }} fail</span>
        @if($mismatchCount > 0)
            <span class="pill pill-flag">{{ $mismatchCount }} name mismatch</span>
        @endif
        @if($flaggedCount > $mismatchCount)
            <span class="pill pill-flag">{{ $flaggedCount - $mismatchCount }} missing info</span>
        @endif
    </div>
</div>

{{-- ── Teacher banner ─────────────────────────────────────────────────── --}}
<div class="teacher-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span>Uploading on behalf of <strong>{{ $context['teacher_name'] }}</strong> for <strong>{{ $context['subject_code'] }} — {{ $context['section'] }}</strong>. Confirm all info is correct before saving.</span>
</div>

{{-- ── Flagged rows notice ─────────────────────────────────────────────── --}}
@if($flaggedCount > 0)
<div class="flag-info">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <span>Rows highlighted in yellow need attention — <strong>missing name or code</strong> will be skipped on save, and <strong>name mismatches</strong> show the current database value below the input.</span>
</div>
@endif

{{-- ── Item matrix status banner ───────────────────────────────────────── --}}
@if(!empty($matrixData) && ($matrixData['total_items'] ?? 0) > 0)
    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f0faf7;border:1px solid #9fe1cb;border-radius:8px;font-size:13px;color:var(--teal);margin-bottom:16px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span>Item analysis matrix parsed — <strong>{{ $matrixData['total_items'] }}</strong> items detected. Review the matrix below and add any missing item numbers before saving.</span>
    </div>
@else
    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fff8f0;border:1px solid #f0c84a;border-radius:8px;font-size:13px;color:var(--amber);margin-bottom:16px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span>No item analysis PDF uploaded — matrix will be skipped.</span>
    </div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════
     Item Analysis Matrix (editable)
═════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($matrixData) && ($matrixData['total_items'] ?? 0) > 0)
@php
    $discCols  = \App\Services\ItemMatrixParser::DISCRIMINATION_COLS;
    $diffBands = \App\Services\ItemMatrixParser::DIFFICULTY_BANDS;

    /* encode for JS — safe to embed in a <script> tag */
    $matrixJson = json_encode([
        'title'         => $matrixData['title']      ?? '',
        'module'        => $matrixData['module']      ?? '',
        'date'          => $matrixData['date']        ?? '',
        'cells'         => $matrixData['cells']       ?? [],
        'row_totals'    => $matrixData['row_totals']  ?? [],
        'col_totals'    => $matrixData['col_totals']  ?? [],
        'total_items'   => $matrixData['total_items'] ?? 0,
        'legend'        => $matrixData['legend']      ?? [],
        'disc_columns'  => $discCols,
        'diff_bands'    => $diffBands,
    ]);
@endphp

<div class="matrix-card" id="matrix-card">

    {{-- header --}}
    <div class="card-header">
        <div>
            <div class="card-title">Item analysis matrix</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">
                @if($matrixData['title'])  <span class="pill pill-total" style="font-size:10px">{{ $matrixData['title'] }}</span>  @endif
                @if($matrixData['module']) <span class="pill pill-total" style="font-size:10px">{{ $matrixData['module'] }}</span> @endif
                @if($matrixData['date'])   <span class="pill pill-total" style="font-size:10px">{{ $matrixData['date'] }}</span>   @endif
            </div>
        </div>
        <div class="summary-pills" id="matrix-header-pills">
            {{-- filled by JS --}}
        </div>
    </div>

    {{-- stat bar --}}
    <div class="matrix-stat-bar">
        <div class="matrix-stat-item">
            <div class="matrix-stat-label">Total items</div>
            <div class="matrix-stat-val sv-total" id="ms-total">{{ $matrixData['total_items'] }}</div>
            <div class="matrix-stat-delta" id="ms-total-d"></div>
        </div>
        <div class="matrix-stat-item">
            <div class="matrix-stat-label">Acceptable</div>
            <div class="matrix-stat-val sv-accept" id="ms-accept">0</div>
            <div class="matrix-stat-delta" id="ms-accept-d"></div>
        </div>
        <div class="matrix-stat-item">
            <div class="matrix-stat-label">Needs revision</div>
            <div class="matrix-stat-val sv-revise" id="ms-revise">0</div>
            <div class="matrix-stat-delta" id="ms-revise-d"></div>
        </div>
        <div class="matrix-stat-item">
            <div class="matrix-stat-label">Reject</div>
            <div class="matrix-stat-val sv-reject" id="ms-reject">0</div>
            <div class="matrix-stat-delta" id="ms-reject-d"></div>
        </div>
    </div>

    {{-- edit toolbar --}}
    <div class="matrix-edit-toolbar" id="matrix-toolbar">
        <span class="matrix-edit-hint" id="matrix-edit-hint">Click "Edit matrix" to add or remove item numbers directly in the table.</span>
        <div class="matrix-btn-group" id="matrix-btn-group">
            <button type="button" class="mbtn mbtn-edit" onclick="matrixStartEdit()">Edit matrix</button>
        </div>
    </div>

    {{-- table --}}
    <div class="matrix-wrap">
        <table class="matrix-table">
            <thead id="matrix-thead"></thead>
            <tbody id="matrix-tbody"></tbody>
            <tfoot id="matrix-tfoot"></tfoot>
        </table>
    </div>

    {{-- legend --}}
    <div class="matrix-legend" id="matrix-legend"></div>

    {{-- confirm overlay (inside card so position:absolute works) --}}
    <div class="confirm-overlay" id="matrix-confirm-overlay">
        <div class="confirm-box">
            <div class="confirm-title">Confirm matrix changes</div>
            <div class="confirm-sub">Review what will be added or removed before applying.</div>
            <div class="confirm-diff" id="matrix-confirm-diff">No changes.</div>
            <div class="confirm-btns">
                <button type="button" class="mbtn mbtn-cancel" onclick="matrixCancelEdit()">Cancel</button>
                <button type="button" class="mbtn mbtn-confirm" onclick="matrixApplyEdit()">Apply changes</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
/* ── Constants from PHP ─────────────────────────────────────── */
const DISC_COLS = @json($discCols);
const DIFF_BANDS = @json($diffBands); /* { band: label, ... } */
const DIFF_BAND_KEYS = Object.keys(DIFF_BANDS);
const INITIAL = @json(json_decode($matrixJson, true));

/* ── State ──────────────────────────────────────────────────── */
let liveData   = deepClone(INITIAL.cells);   /* working copy */
let editData   = null;                        /* copy while editing */
let origData   = null;                        /* snapshot before edit */
let isEditing  = false;

function deepClone(o){ return JSON.parse(JSON.stringify(o)); }

/* ── Chip / column helpers ──────────────────────────────────── */
function colCat(col){
    if(['<.00','.00-.14'].includes(col))    return 'r';
    if(['.15-.24','.25-.29'].includes(col)) return 'n';
    return 'a';
}
function chipCls(col){ return 'item-chip chip-'+colCat(col); }
function subHtml(col){
    const map={r:'Reject',n:'Revise',a:'Accept'};
    return `<span class="m-sub m-sub-${colCat(col)}">${map[colCat(col)]}</span>`;
}

/* ── Stats ──────────────────────────────────────────────────── */
function computeStats(cells){
    let total=0,accept=0,revise=0,reject=0;
    DIFF_BAND_KEYS.forEach(b=>{
        DISC_COLS.forEach(c=>{
            const n=((cells[b]||{})[c]||[]).length;
            total+=n;
            const cat=colCat(c);
            if(cat==='a') accept+=n;
            if(cat==='n') revise+=n;
            if(cat==='r') reject+=n;
        });
    });
    return {total,accept,revise,reject};
}

function renderStats(cells, origCells){
    const s=computeStats(cells);
    const o=origCells?computeStats(origCells):null;
    const pairs=[
        ['ms-total','ms-total-d',s.total,o?.total],
        ['ms-accept','ms-accept-d',s.accept,o?.accept],
        ['ms-revise','ms-revise-d',s.revise,o?.revise],
        ['ms-reject','ms-reject-d',s.reject,o?.reject],
    ];
    pairs.forEach(([vid,did,val,oval])=>{
        document.getElementById(vid).textContent=val;
        const del=document.getElementById(did);
        if(oval!==undefined && val!==oval){
            const d=val-oval;
            del.textContent=(d>0?'+':'')+d+' from parsed';
            del.className='matrix-stat-delta '+(d>0?'delta-add':'delta-rem');
        } else {
            del.textContent='';
            del.className='matrix-stat-delta';
        }
    });
    /* header pills */
    document.getElementById('matrix-header-pills').innerHTML=`
        <span class="pill pill-total">${s.total} items</span>
        <span class="pill pill-pass">${s.accept} acceptable</span>
        <span class="pill pill-flag">${s.revise} revise</span>
        <span class="pill pill-fail">${s.reject} reject</span>`;
}

/* ── Table render ───────────────────────────────────────────── */
function renderHead(){
    const thead=document.getElementById('matrix-thead');
    thead.innerHTML='<tr><th class="mth-left">Difficulty</th>'
        +DISC_COLS.map(c=>`<th>${c}${subHtml(c)}</th>`).join('')
        +'<th>Total</th></tr>';
}

function renderBody(cells, editable){
    const tbody=document.getElementById('matrix-tbody');
    tbody.innerHTML='';
    DIFF_BAND_KEYS.forEach(band=>{
        const tr=document.createElement('tr');
        let rowTotal=0;
        let html=`<td class="diff-label">${band}<span class="diff-sub-label">${DIFF_BANDS[band]}</span></td>`;
        DISC_COLS.forEach(col=>{
            const items=((cells[band]||{})[col]||[]);
            rowTotal+=items.length;
            if(editable){
                html+=`<td data-band="${band}" data-col="${col}">
                    <div class="item-chips" id="ichips-${CSS.escape(band+'-'+col)}">
                        ${chipListHtml(items,col,true)}
                    </div>
                    <div class="cell-add-row">
                        <input class="cell-num-input" type="number" min="1" max="999" placeholder="#"
                            id="inp-${CSS.escape(band+'-'+col)}"
                            onkeydown="if(event.key==='Enter'){window._matrixAdd('${band}','${col}');event.preventDefault()}">
                        <button type="button" class="cell-add-go"
                            onclick="window._matrixAdd('${band}','${col}')">Add</button>
                    </div>
                </td>`;
            } else {
                html+=`<td><div class="item-chips">${
                    items.length
                        ? chipListHtml(items,col,false)
                        : '<span class="empty-cross">×</span>'
                }</div></td>`;
            }
        });
        html+=`<td class="total-cell" id="rtotal-${CSS.escape(band)}">${rowTotal}</td>`;
        tr.innerHTML=html;
        tbody.appendChild(tr);
    });
}

function chipListHtml(items, col, deletable){
    return items.map(it=>`<span class="${chipCls(col)}">${it}${
        deletable
            ? `<span class="chip-del" onclick="window._matrixDel('${it}','${col}','${escBand(it,col)}')">×</span>`
            : ''
    }</span>`).join('');
}

/* band key embedded differently — use a data approach */
function escBand(item, col){
    /* We'll look up the band from editData instead */
    return '';
}

function renderFoot(cells){
    const colTotals={};
    let grand=0;
    DISC_COLS.forEach(c=>{ colTotals[c]=0; });
    DIFF_BAND_KEYS.forEach(b=>{
        DISC_COLS.forEach(c=>{
            const n=((cells[b]||{})[c]||[]).length;
            colTotals[c]+=n; grand+=n;
        });
    });
    document.getElementById('matrix-tfoot').innerHTML=
        `<tr><td class="mft-label">Total</td>`
        +DISC_COLS.map(c=>`<td>${colTotals[c]}</td>`).join('')
        +`<td>${grand}</td></tr>`;
}

function renderLegend(cells){
    const counts={r:0,n:0,a:0};
    DIFF_BAND_KEYS.forEach(b=>{
        DISC_COLS.forEach(c=>{
            counts[colCat(c)]+=((cells[b]||{})[c]||[]).length;
        });
    });
    document.getElementById('matrix-legend').innerHTML=`
        <span class="legend-item"><span class="legend-dot ld-r"></span>Reject (&lt;.00–.00-.14): <span class="legend-count lc-r">${counts.r}</span></span>
        <span class="legend-item"><span class="legend-dot ld-n"></span>Needs revision (.15-.24–.25-.29): <span class="legend-count lc-n">${counts.n}</span></span>
        <span class="legend-item"><span class="legend-dot ld-a"></span>Acceptable (.30-.44–.45+): <span class="legend-count lc-a">${counts.a}</span></span>`;
}

function renderAll(cells, orig, editable){
    renderHead();
    renderBody(cells, editable);
    renderFoot(cells);
    renderLegend(cells);
    renderStats(cells, orig);
    syncHiddenInput(cells);
}

/* ── Sync hidden form input ─────────────────────────────────── */
function syncHiddenInput(cells){
    /* Rebuild a full matrixData shape for the server */
    const rowTotals={}, colTotals={};
    let grand=0;
    const legend={reject:[],needs_revision:[],acceptable:[]};
    DISC_COLS.forEach(c=>{ colTotals[c]=0; });
    DIFF_BAND_KEYS.forEach(b=>{
        rowTotals[b]=0;
        DISC_COLS.forEach(c=>{
            const items=((cells[b]||{})[c]||[]);
            rowTotals[b]+=items.length;
            colTotals[c]+=items.length;
            grand+=items.length;
            const cat=colCat(c);
            if(cat==='r') items.forEach(i=>legend.reject.push(i));
            if(cat==='n') items.forEach(i=>legend.needs_revision.push(i));
            if(cat==='a') items.forEach(i=>legend.acceptable.push(i));
        });
    });
    const payload={
        title:      INITIAL.title,
        module:     INITIAL.module,
        date:       INITIAL.date,
        disc_columns: DISC_COLS,
        rows: DIFF_BAND_KEYS.map(b=>({
            difficulty: b,
            label:      DIFF_BANDS[b],
            columns:    cells[b]||{},
            total:      rowTotals[b],
        })),
        column_totals: colTotals,
        grand_total:   grand,
        cells,
        row_totals: rowTotals,
        col_totals: colTotals,
        total_items: grand,
        legend,
    };
    document.getElementById('item_matrix_edited_json').value=JSON.stringify(payload);
}

/* ── Row total refresh (edit mode) ─────────────────────────── */
function refreshRowTotal(band){
    const total=DISC_COLS.reduce((s,c)=>s+(((editData||{})[band]||{})[c]||[]).length,0);
    const el=document.getElementById('rtotal-'+CSS.escape(band));
    if(el) el.textContent=total;
}

/* ── Cell chips refresh (edit mode) ────────────────────────── */
function refreshCell(band, col){
    const items=((editData||{})[band]||{})[col]||[];
    const wrap=document.getElementById('ichips-'+CSS.escape(band+'-'+col));
    if(!wrap) return;
    wrap.innerHTML=chipListHtml(items, col, true);
}

/* ── Global handlers (needed for inline onclick) ────────────── */
window._matrixDel = function(item, col, _unused){
    /* Find which band this item+col lives in */
    if(!editData) return;
    DIFF_BAND_KEYS.forEach(band=>{
        const arr=(editData[band]||{})[col]||[];
        const idx=arr.indexOf(Number(item));
        if(idx>-1){
            arr.splice(idx,1);
            refreshCell(band,col);
            refreshRowTotal(band);
            renderFoot(editData);
            renderStats(editData,origData);
            renderLegend(editData);
        }
    });
};

window._matrixAdd = function(band, col){
    const inp=document.getElementById('inp-'+CSS.escape(band+'-'+col));
    if(!inp) return;
    const val=parseInt(inp.value);
    if(!val||val<1||val>9999){ inp.value=''; return; }
    if(!editData[band]) editData[band]={};
    if(!editData[band][col]) editData[band][col]=[];
    if(!editData[band][col].includes(val)){
        editData[band][col].push(val);
        editData[band][col].sort((a,b)=>a-b);
    }
    inp.value='';
    refreshCell(band,col);
    refreshRowTotal(band);
    renderFoot(editData);
    renderStats(editData,origData);
    renderLegend(editData);
};

/* ── Edit lifecycle ─────────────────────────────────────────── */
window.matrixStartEdit = function(){
    isEditing=true;
    origData=deepClone(liveData);
    editData=deepClone(liveData);
    document.getElementById('matrix-toolbar').classList.add('editing');
    document.getElementById('matrix-edit-hint').textContent='Enter an item number and click Add, or × to remove a chip. Click Confirm when done.';
    document.getElementById('matrix-edit-hint').classList.add('active');
    document.getElementById('matrix-btn-group').innerHTML=`
        <button type="button" class="mbtn mbtn-cancel" onclick="matrixCancelEdit()">Cancel</button>
        <button type="button" class="mbtn mbtn-confirm" onclick="matrixShowConfirm()">Confirm changes</button>`;
    renderAll(editData, origData, true);
};

window.matrixCancelEdit = function(){
    isEditing=false;
    editData=null;
    origData=null;
    document.getElementById('matrix-toolbar').classList.remove('editing');
    document.getElementById('matrix-edit-hint').textContent='Click "Edit matrix" to add or remove item numbers directly in the table.';
    document.getElementById('matrix-edit-hint').classList.remove('active');
    document.getElementById('matrix-btn-group').innerHTML=`<button type="button" class="mbtn mbtn-edit" onclick="matrixStartEdit()">Edit matrix</button>`;
    document.getElementById('matrix-confirm-overlay').classList.remove('show');
    renderAll(liveData, null, false);
};

window.matrixShowConfirm = function(){
    const added=[], removed=[];
    DIFF_BAND_KEYS.forEach(band=>{
        DISC_COLS.forEach(col=>{
            const orig=((origData||{})[band]||{})[col]||[];
            const cur=((editData||{})[band]||{})[col]||[];
            cur.forEach(it=>{ if(!orig.includes(it)) added.push({band,col,it}); });
            orig.forEach(it=>{ if(!cur.includes(it)) removed.push({band,col,it}); });
        });
    });
    let html='';
    if(!added.length&&!removed.length){
        html='<span style="color:var(--text-soft)">No changes made.</span>';
    }
    if(added.length){
        html+=`<div><span class="diff-add">+ Added (${added.length}):</span> ${added.map(x=>`<strong>${x.it}</strong>`).join(', ')}</div>`;
    }
    if(removed.length){
        html+=`<div style="margin-top:4px"><span class="diff-rem">− Removed (${removed.length}):</span> ${removed.map(x=>`<strong>${x.it}</strong>`).join(', ')}</div>`;
    }
    document.getElementById('matrix-confirm-diff').innerHTML=html;
    document.getElementById('matrix-confirm-overlay').classList.add('show');
};

window.matrixApplyEdit = function(){
    liveData=deepClone(editData);
    isEditing=false;
    editData=null;
    origData=null;
    document.getElementById('matrix-toolbar').classList.remove('editing');
    document.getElementById('matrix-edit-hint').textContent='Changes applied. Click "Edit matrix" to make further edits.';
    document.getElementById('matrix-edit-hint').classList.remove('active');
    document.getElementById('matrix-btn-group').innerHTML=`<button type="button" class="mbtn mbtn-edit" onclick="matrixStartEdit()">Edit matrix</button>`;
    document.getElementById('matrix-confirm-overlay').classList.remove('show');
    renderAll(liveData, null, false);
};

/* ── Bootstrap ──────────────────────────────────────────────── */
renderAll(liveData, null, false);
})();
</script>
@endif

{{-- ════════════════════════════════════════════════════════════════════════
     Student results table
═════════════════════════════════════════════════════════════════════════ --}}
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <span class="card-title">Extracted results — {{ $totalRows }} students</span>
        <span class="card-sub">Review and correct flagged rows before saving</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:40px;text-align:center">#</th>
                <th>Student name</th>
                <th>Student code</th>
                <th>Raw score</th>
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
                        @if(!empty($row['mismatch']))
                            <div class="db-name-hint">DB has: <strong>{{ $row['db_name'] }}</strong> — edit to correct, or leave to overwrite</div>
                        @endif
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
                        <span class="pct {{ $row['remark'] === 'fail' ? 'pct-fail' : 'pct-pass' }}">{{ $row['percentage'] }}%</span>
                    </div>
                </td>

                <td>
                    <div class="td-inner">
                        <span class="badge badge-{{ $row['remark'] }}">{{ ucfirst($row['remark']) }}</span>
                    </div>
                </td>

                <td>
                    <div class="td-inner">
                        @if(!empty($row['mismatch']))
                            <span class="flag-badge badge-mismatch">⚠ Name mismatch</span>
                        @elseif($row['flagged'])
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
