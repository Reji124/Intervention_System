{{-- resources/views/admin/interventions/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Intervention Report')
@section('page-title', 'Intervention Report')

@push('styles')
<style>
/* ── Layout & header ──────────────────────────────────────────────────────── */
.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px; }
.page-header h1 { font-family:'DM Serif Display',serif;font-size:26px;color:var(--text-dark);margin-bottom:4px; }
.page-header p { font-size:13px;color:var(--text-soft); }
.summary-pills { display:flex;gap:10px;align-items:center; }
.spill { display:flex;flex-direction:column;align-items:center;padding:8px 16px;border-radius:10px;min-width:76px; }
.spill-val   { font-family:'DM Serif Display',serif;font-size:22px;line-height:1; }
.spill-label { font-size:10px;text-transform:uppercase;letter-spacing:.6px;margin-top:2px;opacity:.75; }
.spill-fail  { background:var(--red-bg);color:var(--red); }
.spill-pass  { background:var(--green-bg);color:var(--green); }

/* header action buttons */
.header-actions { display:flex;gap:8px;align-items:center;flex-wrap:wrap; }
.btn-icon { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:8px;font-size:12px;font-weight:500;border:1.5px solid var(--border);color:var(--text-mid);background:var(--white);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-icon:hover { border-color:var(--text-mid); }
.btn-icon svg { width:14px;height:14px; }
.btn-icon.btn-export { border-color:#b5d4f4;color:var(--blue);background:#f0f5ff; }
.btn-icon.btn-export:hover { background:#dbeafe; }
.btn-icon.btn-danger { border-color:#f5c6c6;color:var(--red);background:var(--red-bg); }
.btn-icon.btn-danger:hover { background:#fde8e8;border-color:var(--red); }
.btn-icon.btn-danger:disabled { opacity:.45;cursor:not-allowed; }

/* ── Filter panel ─────────────────────────────────────────────────────────── */
.filter-panel { background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px 24px;margin-bottom:24px; }
.filter-panel-title { font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:4px; }
.filter-panel-sub { font-size:12px;color:var(--text-soft);margin-bottom:16px; }
.filter-grid { display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px; }
.filter-group { display:flex;flex-direction:column;gap:6px; }
.filter-group label { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft); }
.filter-group select { padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:13px;background:#faf8f5;border:1.5px solid var(--border);border-radius:8px;color:var(--text-dark);outline:none;transition:border-color .2s; }
.filter-group select:focus { border-color:var(--gold);background:var(--white); }
.filter-actions { display:flex;align-items:center;gap:10px;padding-top:16px;border-top:1px solid var(--border); }
.btn-apply { padding:10px 24px;background:var(--navy);color:var(--white);border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer; }
.btn-apply:hover { background:#1e3050; }
.btn-reset { padding:10px 18px;background:transparent;color:var(--text-mid);border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;cursor:pointer;text-decoration:none;display:inline-block; }
.btn-reset:hover { border-color:var(--text-mid); }
.active-tags { display:flex;flex-wrap:wrap;gap:6px;margin-top:12px; }
.atag { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;background:var(--amber-bg);border:1px solid #f0c84a;border-radius:20px;font-size:11px;color:var(--amber);font-weight:500; }

/* ── Results area ─────────────────────────────────────────────────────────── */
.results-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px; }
.results-count { font-size:13px;color:var(--text-soft); }
.results-count strong { color:var(--text-dark); }
.expand-btn { font-size:12px;color:var(--gold);background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:500;padding:0; }

/* ── Teacher block ────────────────────────────────────────────────────────── */
.teacher-block { background:var(--white);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:12px;transition:box-shadow .2s;animation:fadeIn .3s ease both; }
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.teacher-block:hover { box-shadow:0 2px 14px rgba(0,0,0,.07); }
.teacher-header { padding:16px 22px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;transition:background .15s;flex-wrap:wrap;gap:12px;user-select:none; }
.teacher-header:hover { background:#faf8f5; }
.teacher-info { display:flex;align-items:center;gap:12px; }
.teacher-avatar { width:44px;height:44px;background:var(--navy);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'DM Serif Display',serif;font-size:15px;color:#e8b45a;flex-shrink:0; }
.teacher-name-text { font-family:'DM Serif Display',serif;font-size:16px;color:var(--text-dark); }
.teacher-sub-text  { font-size:12px;color:var(--text-soft);margin-top:2px; }
.teacher-right { display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
.stat-chips { display:flex;gap:8px; }
.chip { display:flex;flex-direction:column;align-items:center;padding:6px 14px;border-radius:8px;min-width:60px; }
.chip-val   { font-family:'DM Serif Display',serif;font-size:20px;line-height:1; }
.chip-label { font-size:10px;text-transform:uppercase;letter-spacing:.6px;margin-top:2px;opacity:.7; }
.chip-pass{background:var(--green-bg);color:var(--green)} .chip-fail{background:var(--red-bg);color:var(--red)} .chip-rate{background:var(--amber-bg);color:var(--amber)} .chip-total{background:#f0ece3;color:var(--text-mid)}
.toggle-chevron { width:20px;height:20px;color:var(--text-soft);transition:transform .25s;flex-shrink:0; }
.toggle-chevron.open { transform:rotate(180deg); }
.teacher-body { border-top:1px solid var(--border);display:none; }
.teacher-body.open { display:block; }

/* ── Status badge (inline on teacher card) ────────────────────────────────── */
.status-badge-wrap { display:flex;align-items:center;gap:6px; }
.status-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap; }
.status-no_status           { background:#f0ece3;color:#8a7f72; }
.status-on_track            { background:var(--green-bg);color:var(--green); }
.status-needs_followup      { background:var(--amber-bg);color:var(--amber); }
.status-intervention_active { background:var(--red-bg);color:var(--red); }
.status-resolved            { background:#e8f0fe;color:#1a56db; }
/* Notes icon trigger button */
.btn-notes-trigger {
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:7px;
    border:1.5px solid var(--border);background:var(--white);
    color:var(--text-soft);cursor:pointer;transition:all .15s;
    flex-shrink:0;
}
.btn-notes-trigger:hover { border-color:var(--navy);color:var(--navy);background:#f0f4ff; }
.btn-notes-trigger.has-note { border-color:#b5d4f4;color:var(--blue);background:#f0f5ff; }
.btn-notes-trigger svg { width:13px;height:13px; }

/* ── Subject block ────────────────────────────────────────────────────────── */
.subject-block { border-bottom:1px solid #f3efe8; }
.subject-block:last-child { border-bottom:none; }
.subject-header { padding:11px 22px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:#fafafa;transition:background .15s;user-select:none; }
.subject-header:hover { background:#f5f0e8; }
.subject-title-text { font-size:13px;font-weight:600;color:var(--text-dark);display:flex;align-items:center;gap:8px; }
.subject-pills { display:flex;gap:6px;align-items:center; }
.subject-body { display:none; }
.subject-body.open { display:block; }

/* uploader badge */
.uploader-badge { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:500;background:#f0ece3;color:var(--text-mid);white-space:nowrap; }
.uploader-badge svg { width:10px;height:10px;flex-shrink:0; }

/* ── Tabs ─────────────────────────────────────────────────────────────────── */
.subject-tabs { display:flex;border-bottom:1px solid var(--border);background:#fafafa; }
.subject-tab { padding:10px 20px;font-size:12px;font-weight:600;color:var(--text-soft);cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;user-select:none; }
.subject-tab:hover { color:var(--text-dark); }
.subject-tab.active { color:var(--teal,#1d9e75);border-bottom-color:var(--teal,#1d9e75); }
.tab-panel { display:none; }
.tab-panel.active { display:block; }

/* ── Master list table ────────────────────────────────────────────────────── */
table.master-tbl { width:100%;border-collapse:collapse; }
.master-tbl thead th { font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft);padding:9px 16px;text-align:left;background:#f8f8f8;border-bottom:1px solid var(--border); }
.master-tbl tbody td { padding:9px 16px;font-size:13px;border-bottom:1px solid #f3efe8;color:var(--text-mid);vertical-align:middle; }
.master-tbl tbody tr:last-child td { border-bottom:none; }
.master-tbl tbody tr:hover td { background:#faf8f5; }
.td-name { font-weight:500;color:var(--text-dark); }
.td-code { font-size:11px;color:var(--text-soft);margin-top:1px; }
.pct-fail { font-weight:600;color:var(--red); }
.pct-pass { font-weight:600;color:var(--green); }
.badge { display:inline-block;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px; }
.badge-pass{background:var(--green-bg);color:var(--green)} .badge-fail{background:var(--red-bg);color:var(--red)}
.badge-prelim{background:var(--amber-bg);color:var(--amber)} .badge-midterm{background:var(--blue-bg);color:var(--blue)} .badge-final{background:#f0ebfa;color:#534ab7}
.row-actions { display:flex;gap:6px;align-items:center; }
.btn-edit-row { display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font-size:11px;font-weight:600;background:#f0f5ff;color:var(--blue);border:1px solid #b5d4f4;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-edit-row:hover { background:#dbeafe; }
.btn-edit-row svg { width:11px;height:11px; }
.btn-del-row { display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font-size:11px;font-weight:600;background:var(--red-bg);color:var(--red);border:1px solid #f5c6c6;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-del-row:hover { background:#fde8e8; }
.btn-del-row svg { width:11px;height:11px; }
.btn-delete-exam { display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:7px;font-size:11px;font-weight:600;background:var(--red-bg);color:var(--red);border:1px solid #f5c6c6;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-delete-exam:hover { background:#fde8e8;border-color:var(--red); }
.btn-delete-exam svg { width:11px;height:11px; }

/* ── Matrix ───────────────────────────────────────────────────────────────── */
.matrix-wrap-inner { overflow-x:auto;padding:16px 22px; }
table.matrix-tbl { width:100%;border-collapse:collapse;min-width:560px; }
.matrix-tbl thead th { font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.75);padding:8px 10px;text-align:center;background:var(--navy);border:1px solid rgba(255,255,255,.08);white-space:nowrap; }
.matrix-tbl thead th:first-child { text-align:left;min-width:130px; }
.matrix-tbl thead .sub-row th { font-size:9px;font-weight:500;padding:3px 10px 7px;background:var(--navy);border-top:none; }
.matrix-tbl tbody td { padding:9px 10px;font-size:11px;border:1px solid var(--border);color:var(--text-mid);text-align:center;vertical-align:top; }
.matrix-tbl tbody td:first-child { text-align:left;font-weight:600;padding-left:14px; }
.matrix-tbl tbody tr:hover td { background:#faf8f5; }
.matrix-tbl .row-total { background:#f5f0e8 !important;font-weight:700;color:var(--text-dark) !important; }
.matrix-tbl .totals-row td { background:var(--navy) !important;color:rgba(255,255,255,.9) !important;font-weight:600;border-color:rgba(255,255,255,.1); }
.matrix-tbl .totals-row td:first-child { color:rgba(255,255,255,.6) !important;font-weight:400; }
.item-chip-sm { display:inline-block;font-size:10px;font-weight:600;padding:1px 5px;border-radius:6px;margin:1px;line-height:1.5; }
.chip-reject{background:#fde8e8;color:#c0392b} .chip-needs-revision{background:#fff3cd;color:#856404} .chip-acceptable{background:#d4edda;color:#1a6e34}
.diff-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block; }
.matrix-legend-row { display:flex;gap:16px;flex-wrap:wrap;padding:10px 22px;border-top:1px solid var(--border);background:#fdfcfa; }
.legend-item { display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-mid); }
.legend-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
.legend-dot.reject{background:#c0392b} .legend-dot.needs-revision{background:#856404} .legend-dot.acceptable{background:#1a6e34}
.legend-count { font-weight:700;margin-left:2px; }
.legend-count.reject{color:#c0392b} .legend-count.needs-revision{color:#856404} .legend-count.acceptable{color:#1a6e34}

.empty-state { text-align:center;padding:60px;background:var(--white);border:1px solid var(--border);border-radius:12px; }
.empty-state h3 { font-family:'DM Serif Display',serif;font-size:20px;color:var(--text-mid);margin-bottom:8px; }
.empty-state p { font-size:13px;color:var(--text-soft); }

/* ── Edit result modal ────────────────────────────────────────────────────── */
.modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;display:flex;align-items:center;justify-content:center;padding:20px; }
.modal-backdrop.hidden { display:none; }
.modal { background:var(--white);border-radius:14px;width:100%;max-width:420px;overflow:hidden;animation:modalIn .2s ease both; }
@keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
.modal-header { padding:18px 22px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
.modal-title { font-family:'DM Serif Display',serif;font-size:17px;color:var(--text-dark); }
.modal-close { width:28px;height:28px;border-radius:50%;border:none;background:#f0ece3;color:var(--text-mid);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;line-height:1; }
.modal-close:hover { background:var(--border); }
.modal-body { padding:20px 22px;display:flex;flex-direction:column;gap:14px; }
.modal-field { display:flex;flex-direction:column;gap:5px; }
.modal-field label { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft); }
.modal-field input,.modal-field select,.modal-field textarea {
    padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:13px;
    background:#faf8f5;border:1.5px solid var(--border);border-radius:8px;
    color:var(--text-dark);outline:none;transition:border-color .2s;
}
.modal-field input:focus,.modal-field select:focus,.modal-field textarea:focus { border-color:var(--teal-light);background:var(--white); }
.modal-field textarea { resize:vertical;min-height:100px; }
.modal-preview { display:flex;gap:10px;padding:10px 14px;background:#f0faf7;border:1px solid #9fe1cb;border-radius:8px;font-size:12px;color:var(--teal); }
.modal-preview span { font-weight:600; }
.modal-footer { padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end; }
.btn-modal-cancel { padding:9px 18px;background:transparent;color:var(--text-mid);border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;cursor:pointer; }
.btn-modal-save { padding:9px 20px;background:var(--navy);color:var(--white);border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer; }
.btn-modal-save:hover { background:#1e3050; }
.btn-modal-save:disabled { opacity:.6;cursor:not-allowed; }

/* ── Notes card (slide-over panel) ───────────────────────────────────────── */
.notes-panel-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.38);z-index:8500;display:flex;align-items:flex-start;justify-content:flex-end;padding:16px; }
.notes-panel-backdrop.hidden { display:none; }
.notes-panel {
    background:var(--white);border-radius:14px;width:100%;max-width:440px;
    max-height:calc(100vh - 32px);overflow-y:auto;
    animation:panelIn .22s ease both;
    box-shadow:0 8px 40px rgba(0,0,0,.18);
    display:flex;flex-direction:column;
}
@keyframes panelIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
.notes-panel-header { padding:18px 22px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0; }
.notes-panel-title { font-family:'DM Serif Display',serif;font-size:17px;color:var(--text-dark); }
.notes-panel-teacher { font-size:12px;color:var(--text-soft);margin-top:2px; }
.notes-panel-body { padding:20px 22px;display:flex;flex-direction:column;gap:16px;flex:1; }
.notes-section-title {
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
    color:var(--text-soft);margin-bottom:6px;
}
/* Status: row */
.notes-status-label {
    display:block;font-size:22px;font-family:'DM Serif Display',serif;
    color:var(--text-dark);margin-bottom:4px;
}
.status-select-group { display:flex;flex-direction:column;gap:6px; }
.status-option-row { display:grid;grid-template-columns:1fr 1fr;gap:8px; }
.status-opt {
    display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:9px;
    border:1.5px solid var(--border);cursor:pointer;transition:all .15s;
    font-size:12px;font-weight:500;color:var(--text-mid);user-select:none;
}
.status-opt input[type=radio] { display:none; }
.status-opt:hover { border-color:var(--navy);color:var(--navy); }
.status-opt.selected { border-color:currentColor; }
.status-opt.selected.s-no_status           { background:#f0ece3;color:#8a7f72;border-color:#c8bfb3; }
.status-opt.selected.s-on_track            { background:var(--green-bg);color:var(--green);border-color:var(--green); }
.status-opt.selected.s-needs_followup      { background:var(--amber-bg);color:var(--amber);border-color:var(--amber); }
.status-opt.selected.s-intervention_active { background:var(--red-bg);color:var(--red);border-color:var(--red); }
.status-opt.selected.s-resolved            { background:#e8f0fe;color:#1a56db;border-color:#1a56db; }
.status-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
/* Notes: row */
.notes-body-label {
    display:block;font-size:22px;font-family:'DM Serif Display',serif;
    color:var(--text-dark);margin-bottom:4px;
}
.notes-textarea {
    width:100%;padding:10px 12px;font-family:'DM Sans',sans-serif;font-size:13px;
    background:#faf8f5;border:1.5px solid var(--border);border-radius:8px;
    color:var(--text-dark);outline:none;transition:border-color .2s;
    resize:vertical;min-height:120px;box-sizing:border-box;
}
.notes-textarea:focus { border-color:var(--teal-light);background:var(--white); }
.notes-meta { font-size:11px;color:var(--text-soft);margin-top:4px; }
.notes-panel-footer { padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;flex-shrink:0; }

/* ── Mass delete confirm modal ────────────────────────────────────────────── */
.mass-modal { max-width:460px; }
.mass-modal .warning-icon { width:44px;height:44px;background:var(--red-bg);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px; }
.mass-modal .warning-icon svg { width:22px;height:22px;color:var(--red); }
.mass-modal h3 { font-family:'DM Serif Display',serif;font-size:17px;color:var(--text-dark);margin-bottom:6px; }
.mass-modal p { font-size:13px;color:var(--text-soft);line-height:1.6; }
.export-gate { padding:14px;background:var(--amber-bg);border:1px solid #f0c84a;border-radius:8px;margin-top:14px;font-size:12px;color:var(--amber); }
.export-gate strong { display:block;font-size:13px;margin-bottom:4px; }
.btn-export-gate { display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:8px 16px;border-radius:7px;font-size:12px;font-weight:600;background:#fff8e1;border:1px solid #f0c84a;color:var(--amber);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-export-gate:hover { background:#fff3cd; }
.export-confirmed { display:flex;align-items:center;gap:6px;font-size:12px;color:var(--green);margin-top:8px; }
.btn-modal-delete { padding:9px 20px;background:var(--red);color:var(--white);border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer; }
.btn-modal-delete:hover { background:#c0392b; }
.btn-modal-delete:disabled { opacity:.5;cursor:not-allowed; }

@media print {
    .filter-panel,.btn-icon,.expand-btn,.row-actions,.btn-delete-exam,
    .btn-notes-trigger,.status-badge-wrap { display:none !important; }
    /* Only show status badge text in print, not the button */
    .status-badge { display:inline-flex !important; }
    .teacher-body,.subject-body,.tab-panel { display:block !important; }
    .sidebar,.topbar { display:none !important; }
    .main { margin-left:0 !important; }
}
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="page-header">
    <div>
        <h1>Intervention Report</h1>
        <p>Browse exam results, student scores, and item analysis by subject</p>
    </div>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <div class="summary-pills">
            <div class="spill spill-fail"><span class="spill-val">{{ $totalFailing }}</span><span class="spill-label">Failing</span></div>
            <div class="spill spill-pass"><span class="spill-val">{{ $totalPassing }}</span><span class="spill-label">Passing</span></div>
        </div>
        <div class="header-actions">
            {{-- Export CSV --}}
            <button class="btn-icon btn-export" id="export-btn" onclick="exportFiltered()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </button>
            {{-- Mass Delete --}}
            @if(!$grouped->isEmpty())
            <button class="btn-icon btn-danger" id="mass-delete-btn" onclick="openMassDelete()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Delete filtered
            </button>
            @endif
            {{-- Print --}}
            <button class="btn-icon" onclick="printFiltered()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print
            </button>
        </div>
    </div>
</div>

{{-- ═══ FILTER PANEL ═══ --}}
<div class="filter-panel">
    <div class="filter-panel-title">Filter results</div>
    <div class="filter-panel-sub">Defaults to the current semester.</div>

    <form method="GET" action="{{ route('admin.interventions.index') }}" id="filter-form">
        <input type="hidden" name="_filtered" value="1">
        <div class="filter-grid">

            <div class="filter-group">
                <label>School year</label>
                <select name="school_year_id" id="f-sy">
                    <option value="">All school years</option>
                    @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ $selectedSY == $sy->id ? 'selected' : '' }}>
                        S.Y. {{ $sy->year_start }}–{{ $sy->year_end }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Semester</label>
                <select name="semester_id" id="f-sem">
                    <option value="">All semesters</option>
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}"
                        data-sy="{{ $sem->school_year_id }}"
                        {{ $selectedSem == $sem->id ? 'selected' : '' }}>
                        {{ $sem->semester_name }} Sem
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Department</label>
                <select name="department_id" id="f-dept">
                    <option value="">All departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $selectedDept == $dept->id ? 'selected' : '' }}>
                        {{ $dept->department_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Category</label>
                <select name="category" id="f-cat">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $selectedCat == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Subject</label>
                <select name="subject_id" id="f-subj">
                    <option value="">All subjects</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}"
                        data-dept="{{ $subj->department_id }}"
                        data-cat="{{ $subj->category }}"
                        {{ $selectedSubject == $subj->id ? 'selected' : '' }}>
                        {{ $subj->subject_code }} — {{ $subj->subject_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Teacher</label>
                <select name="teacher_id" id="f-teacher">
                    <option value="">All teachers</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                        data-dept="{{ implode(',', $teacher->teacherSubjects->pluck('subject.department_id')->unique()->filter()->values()->toArray()) }}"
                        data-sem="{{ implode(',', $teacher->teacherSubjects->pluck('semester_id')->unique()->filter()->values()->toArray()) }}"
                        {{ $selectedTeacher == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->teacher_name }}
                    </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-apply">Apply filters</button>
            <a href="{{ route('admin.interventions.index') }}" class="btn-reset">Reset</a>
        </div>

        @php
            $hasFilters = $selectedSY || $selectedDept || $selectedCat
                       || $selectedSubject || $selectedTeacher
                       || ($selectedSem && $selectedSem != $activeSemester?->id);
        @endphp

        @if($hasFilters)
        <div class="active-tags">
            @if($selectedSY)   @php $sy   = $schoolYears->find($selectedSY)  @endphp
                @if($sy)   <span class="atag">S.Y. {{ $sy->year_start }}–{{ $sy->year_end }}</span> @endif
            @endif
            @if($selectedSem)  @php $sem  = $semesters->find($selectedSem)   @endphp
                @if($sem)  <span class="atag">{{ $sem->semester_name }} Sem</span> @endif
            @endif
            @if($selectedDept) @php $dept = $departments->find($selectedDept) @endphp
                @if($dept) <span class="atag">{{ $dept->department_name }}</span> @endif
            @endif
            @if($selectedCat)     <span class="atag">{{ $selectedCat }}</span> @endif
            @if($selectedSubject) @php $subj = $subjects->find($selectedSubject) @endphp
                @if($subj) <span class="atag">{{ $subj->subject_code }}</span> @endif
            @endif
            @if($selectedTeacher) @php $tchr = $teachers->find($selectedTeacher) @endphp
                @if($tchr) <span class="atag">{{ $tchr->teacher_name }}</span> @endif
            @endif
        </div>
        @else
        <div class="active-tags">
            <span class="atag" style="background:var(--green-bg);border-color:#b7dfc5;color:var(--green)">
                Showing: {{ $activeSemester?->semester_name }} Sem
                S.Y. {{ $activeSemester?->schoolYear?->year_start }}–{{ $activeSemester?->schoolYear?->year_end }} (default)
            </span>
        </div>
        @endif
    </form>
</div>

{{-- ═══ RESULTS ═══ --}}
@if($grouped->isEmpty())
    <div class="empty-state">
        <h3>No exams found</h3>
        <p>No exam results match the current filters, or none have been uploaded yet for this semester.</p>
    </div>
@else

<div class="results-header">
    <p class="results-count">
        <strong>{{ $grouped->count() }}</strong> teacher(s) ·
        <strong>{{ $grouped->flatten(1)->sum('total_count') }}</strong> total results ·
        <strong style="color:var(--red)">{{ $totalFailing }}</strong> failing
    </p>
    <button class="expand-btn" id="expand-all-btn" onclick="expandAll()">Expand all</button>
</div>

@foreach($grouped as $teacherName => $subjectMap)
@php
    $firstSubjectData = $subjectMap->first();
    $teacherObj  = $firstSubjectData['teacher_subject']->teacher;
    $teacherNote = $firstSubjectData['teacher_note'];
    $noteStatus  = $teacherNote?->status ?? 'no_status';
    $noteLabels  = \App\Models\TeacherNote::STATUSES;
    $semId       = $selectedSem ?? $activeSemester?->id;

    $tPass  = $subjectMap->sum('pass_count');
    $tFail  = $subjectMap->sum('fail_count');
    $tTotal = $subjectMap->sum('total_count');
    $tRate  = $tTotal > 0 ? round(($tPass / $tTotal) * 100) : 0;
    $inits  = collect(explode(' ', $teacherName))
                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                ->take(2)->implode('');
@endphp

<div class="teacher-block" id="teacher-block-{{ $teacherObj->id }}">
    <div class="teacher-header" onclick="toggleTeacher(this)">
        <div class="teacher-info">
            <div class="teacher-avatar">{{ $inits }}</div>
            <div>
                <div class="teacher-name-text">{{ $teacherName }}</div>
                <div class="teacher-sub-text">
                    {{ $subjectMap->count() }} subject(s) · {{ $tFail }} failing · {{ $tTotal }} total results
                </div>
            </div>
        </div>
        <div class="teacher-right">
            {{-- Status badge + Notes button (level with chips) --}}
            <div class="status-badge-wrap" onclick="event.stopPropagation()">
                <span class="status-badge status-{{ $noteStatus }}"
                      id="status-badge-{{ $teacherObj->id }}">
                    {{ $noteLabels[$noteStatus] ?? 'No Status' }}
                </span>
                <button class="btn-notes-trigger {{ $teacherNote && $teacherNote->notes ? 'has-note' : '' }}"
                        id="notes-btn-{{ $teacherObj->id }}"
                        title="Teacher notes"
                        onclick="openNotes({{ $teacherObj->id }}, '{{ addslashes($teacherName) }}', {{ $semId ?? 'null' }})">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                </button>
            </div>
            <div class="stat-chips">
                <div class="chip chip-total"><span class="chip-val">{{ $tTotal }}</span><span class="chip-label">Total</span></div>
                <div class="chip chip-pass"><span class="chip-val">{{ $tPass }}</span><span class="chip-label">Passed</span></div>
                <div class="chip chip-fail"><span class="chip-val">{{ $tFail }}</span><span class="chip-label">Failed</span></div>
                <div class="chip chip-rate"><span class="chip-val">{{ $tRate }}%</span><span class="chip-label">Pass rate</span></div>
            </div>
            <svg class="toggle-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
    </div>

    <div class="teacher-body">
        @foreach($subjectMap as $label => $subjectData)
        @php
            $exam       = $subjectData['exam'];
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
            $tabId      = 'tab-' . md5($teacherName . $label);
            $uploaderName = $exam?->uploadedBy?->name ?? null;
        @endphp

        <div class="subject-block">
            <div class="subject-header" onclick="toggleSubject(this)">
                <div class="subject-title-text">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:13px;height:13px;color:var(--text-soft)"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    {{ $label }}
                    @if($hasMatrix)
                    <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;background:var(--green-bg);color:var(--green);padding:1px 7px;border-radius:10px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:9px;height:9px"><polyline points="20 6 9 17 4 12"/></svg>Matrix
                    </span>
                    @endif
                    {{-- Uploader badge --}}
                    @if($uploaderName)
                    <span class="uploader-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {{ $uploaderName }}
                    </span>
                    @endif
                </div>
                <div class="subject-pills">
                    <span class="badge badge-pass" style="padding:3px 9px">{{ $subjectData['pass_count'] }} pass</span>
                    @if($subjectData['fail_count'] > 0)
                    <span class="badge badge-fail" style="padding:3px 9px">{{ $subjectData['fail_count'] }} fail</span>
                    @endif
                    @if($exam)
                    <button class="btn-delete-exam"
                        onclick="event.stopPropagation();deleteExam({{ $exam->id }}, this.closest('.subject-block'))">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        Delete exam
                    </button>
                    @endif
                    <svg class="sub-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--text-soft);transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
            </div>

            <div class="subject-body">
                <div class="subject-tabs">
                    <div class="subject-tab active" onclick="switchTab(this,'{{ $tabId }}-students')">
                        Students ({{ $subjectData['total_count'] }})
                        @if($subjectData['fail_count'] > 0)
                        <span style="display:inline-flex;align-items:center;margin-left:4px;padding:1px 6px;background:var(--red-bg);color:var(--red);border-radius:8px;font-size:10px">{{ $subjectData['fail_count'] }} failing</span>
                        @endif
                    </div>
                    @if($hasMatrix)
                    <div class="subject-tab" onclick="switchTab(this,'{{ $tabId }}-matrix')">Item analysis matrix</div>
                    @endif
                </div>

                {{-- Students tab --}}
                <div id="{{ $tabId }}-students" class="tab-panel active">
                    @if($subjectData['all_results']->count())
                    <table class="master-tbl">
                        <thead>
                            <tr>
                                <th>Student</th><th>Exam</th><th>Raw score</th><th>Percentage</th><th>Remark</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-{{ $tabId }}">
                            @foreach($subjectData['all_results']->sortBy('percentage') as $result)
                            @if(!$result->student) @continue @endif
                            <tr id="row-{{ $result->id }}">
                                <td>
                                    <div class="td-name">{{ $result->student->student_name }}</div>
                                    <div class="td-code">{{ $result->student->student_code }}</div>
                                </td>
                                <td><span class="badge badge-{{ $result->exam->exam_type ?? 'prelim' }}">{{ ucfirst($result->exam->exam_type ?? '—') }}</span></td>
                                <td id="score-{{ $result->id }}">{{ $result->raw_score }}</td>
                                <td><span id="pct-{{ $result->id }}" class="{{ $result->remark === 'fail' ? 'pct-fail' : 'pct-pass' }}">{{ $result->percentage }}%</span></td>
                                <td><span id="badge-{{ $result->id }}" class="badge badge-{{ $result->remark }}">{{ ucfirst($result->remark) }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-edit-row" onclick="openEdit({{ $result->id }}, {{ $result->raw_score }})">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Edit
                                        </button>
                                        <button class="btn-del-row" onclick="deleteResult({{ $result->id }})">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="padding:20px 22px;font-size:13px;color:var(--text-soft)">No results recorded for this subject yet.</div>
                    @endif
                </div>

                {{-- Matrix tab --}}
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
                                        @if(in_array($col,['<.00','.00-.14'])) <span style="color:#f09595">Reject</span>
                                        @elseif(in_array($col,['.15-.24','.25-.29'])) <span style="color:#e8b45a">Revise</span>
                                        @else <span style="color:#9fe1cb">Accept</span>
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
                                                @foreach($row['columns'][$col] as $item)<span class="item-chip-sm {{ $chipClass($col) }}">{{ $item }}</span>@endforeach
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
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

@endif {{-- end $grouped->isEmpty() --}}

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- Edit result modal                                                       --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop hidden" id="edit-modal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit exam result</span>
            <button class="modal-close" onclick="closeEdit()">×</button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label>Raw score</label>
                <input type="number" id="edit-raw" min="0" placeholder="e.g. 28">
            </div>
            <div class="modal-field">
                <label>Total items</label>
                <input type="number" id="edit-total" min="1" placeholder="e.g. 50">
            </div>
            <div class="modal-preview" id="edit-preview" style="display:none">
                Percentage: <span id="preview-pct">—</span> &nbsp;·&nbsp; Remark: <span id="preview-remark">—</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" onclick="closeEdit()">Cancel</button>
            <button class="btn-modal-save" id="save-btn" onclick="saveEdit()">Save changes</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- Notes slide-over panel                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="notes-panel-backdrop hidden" id="notes-panel-backdrop">
    <div class="notes-panel" id="notes-panel">
        <div class="notes-panel-header">
            <div>
                <div class="notes-panel-title">Teacher Notes</div>
                <div class="notes-panel-teacher" id="notes-panel-teacher-name"></div>
            </div>
            <button class="modal-close" onclick="closeNotes()">×</button>
        </div>
        <div class="notes-panel-body">
            {{-- Status section --}}
            <div>
                <div class="notes-section-title">Status</div>
                <span class="notes-status-label">Status:</span>
                <div class="status-select-group">
                    <div class="status-option-row">
                        <label class="status-opt s-no_status" data-val="no_status">
                            <input type="radio" name="note_status" value="no_status">
                            <span class="status-dot" style="background:#c8bfb3"></span>
                            No Status
                        </label>
                        <label class="status-opt s-on_track" data-val="on_track">
                            <input type="radio" name="note_status" value="on_track">
                            <span class="status-dot" style="background:var(--green)"></span>
                            On Track
                        </label>
                    </div>
                    <div class="status-option-row">
                        <label class="status-opt s-needs_followup" data-val="needs_followup">
                            <input type="radio" name="note_status" value="needs_followup">
                            <span class="status-dot" style="background:var(--amber)"></span>
                            Needs Follow-up
                        </label>
                        <label class="status-opt s-intervention_active" data-val="intervention_active">
                            <input type="radio" name="note_status" value="intervention_active">
                            <span class="status-dot" style="background:var(--red)"></span>
                            Intervention Active
                        </label>
                    </div>
                    <div class="status-option-row">
                        <label class="status-opt s-resolved" data-val="resolved">
                            <input type="radio" name="note_status" value="resolved">
                            <span class="status-dot" style="background:#1a56db"></span>
                            Resolved
                        </label>
                        <div></div>{{-- empty cell to keep grid --}}
                    </div>
                </div>
            </div>
            {{-- Notes section --}}
            <div>
                <div class="notes-section-title">Notes</div>
                <span class="notes-body-label">Notes:</span>
                <textarea class="notes-textarea" id="notes-textarea"
                    placeholder="Describe the intervention actions taken, follow-up plans, or observations for this teacher…"></textarea>
                <div class="notes-meta" id="notes-meta"></div>
            </div>
        </div>
        <div class="notes-panel-footer">
            <button class="btn-modal-cancel" onclick="closeNotes()">Cancel</button>
            <button class="btn-modal-save" id="notes-save-btn" onclick="saveNote()">Save note</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- Mass delete modal                                                       --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop hidden" id="mass-modal">
    <div class="modal mass-modal">
        <div class="modal-header">
            <span class="modal-title">Delete filtered data</span>
            <button class="modal-close" onclick="closeMassModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="warning-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h3>This will permanently delete all filtered exam data</h3>
            <p id="mass-delete-summary" style="margin-top:6px"></p>
            <div class="export-gate" id="export-gate">
                <strong>Have you exported this data first?</strong>
                It's strongly recommended to export a CSV backup before deleting. This action cannot be undone.
                <br>
                <button class="btn-export-gate" onclick="exportThenConfirm()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV first
                </button>
                <div class="export-confirmed hidden" id="export-confirmed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><polyline points="20 6 9 17 4 12"/></svg>
                    Export started — you can now delete
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" onclick="closeMassModal()">Cancel</button>
            <button class="btn-modal-delete" id="confirm-mass-delete-btn"
                    onclick="confirmMassDelete()" disabled>
                Yes, delete all
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

// ── Current filter state (passed to export / mass-delete) ─────────────────
const FILTERS = {
    school_year_id: '{{ $selectedSY ?? '' }}',
    semester_id:    '{{ $selectedSem ?? '' }}',
    department_id:  '{{ $selectedDept ?? '' }}',
    category:       '{{ $selectedCat ?? '' }}',
    subject_id:     '{{ $selectedSubject ?? '' }}',
    teacher_id:     '{{ $selectedTeacher ?? '' }}',
};

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
    const subject = tab.closest('.subject-body');
    subject.querySelectorAll('.subject-tab').forEach(t => t.classList.remove('active'));
    subject.querySelectorAll('.tab-panel').forEach(p  => p.classList.remove('active'));
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

// ── Edit result modal ─────────────────────────────────────────────────────
let editingResultId = null;

function openEdit(resultId, rawScore) {
    editingResultId = resultId;
    document.getElementById('edit-raw').value   = rawScore;
    document.getElementById('edit-total').value = '';
    document.getElementById('edit-preview').style.display = 'none';
    document.getElementById('edit-modal').classList.remove('hidden');
    document.getElementById('edit-raw').focus();
}
function closeEdit() {
    document.getElementById('edit-modal').classList.add('hidden');
    editingResultId = null;
}

['edit-raw','edit-total'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        const raw   = parseInt(document.getElementById('edit-raw').value);
        const total = parseInt(document.getElementById('edit-total').value);
        const prev  = document.getElementById('edit-preview');
        if (raw >= 0 && total > 0) {
            const pct    = ((raw / total) * 100).toFixed(2);
            const remark = pct >= 75 ? 'Pass' : 'Fail';
            document.getElementById('preview-pct').textContent    = pct + '%';
            document.getElementById('preview-remark').textContent = remark;
            document.getElementById('preview-remark').style.color = pct >= 75 ? 'var(--green)' : 'var(--red)';
            prev.style.display = 'flex';
        } else {
            prev.style.display = 'none';
        }
    });
});

async function saveEdit() {
    const raw   = parseInt(document.getElementById('edit-raw').value);
    const total = parseInt(document.getElementById('edit-total').value);
    if (isNaN(raw) || isNaN(total) || total < 1) {
        alert('Please enter a valid raw score and total items.');
        return;
    }
    const btn = document.getElementById('save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
        const res  = await fetch(`/admin/exam-results/${editingResultId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ raw_score: raw, total })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById(`score-${editingResultId}`).textContent = data.raw_score;
            const pctEl = document.getElementById(`pct-${editingResultId}`);
            pctEl.textContent = data.percentage + '%';
            pctEl.className = data.remark === 'fail' ? 'pct-fail' : 'pct-pass';
            const badgeEl = document.getElementById(`badge-${editingResultId}`);
            badgeEl.textContent = data.remark.charAt(0).toUpperCase() + data.remark.slice(1);
            badgeEl.className = `badge badge-${data.remark}`;
            closeEdit();
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Save changes';
    }
}

async function deleteResult(resultId) {
    if (!confirm('Delete this student result? This cannot be undone.')) return;
    const res  = await fetch(`/admin/exam-results/${resultId}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    if (data.success) document.getElementById(`row-${resultId}`)?.remove();
}

async function deleteExam(examId, subjectBlock) {
    if (!confirm('Delete this entire exam and ALL student results? This cannot be undone.')) return;
    const res  = await fetch(`/admin/exams/${examId}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    if (data.success) subjectBlock?.remove();
}

document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});

// ── Notes panel ───────────────────────────────────────────────────────────
let notesTeacherId  = null;
let notesSemesterId = null;

const STATUS_LABELS = {
    no_status:           'No Status',
    on_track:            'On Track',
    needs_followup:      'Needs Follow-up',
    intervention_active: 'Intervention Active',
    resolved:            'Resolved',
};

function openNotes(teacherId, teacherName, semesterId) {
    notesTeacherId  = teacherId;
    notesSemesterId = semesterId;

    document.getElementById('notes-panel-teacher-name').textContent = teacherName;

    // Load existing data from the DOM badge
    const badgeEl = document.getElementById(`status-badge-${teacherId}`);
    const classes = badgeEl?.className.split(' ') ?? [];
    const statusClass = classes.find(c => c.startsWith('status-') && c !== 'status-badge');
    const currentStatus = statusClass ? statusClass.replace('status-', '') : 'no_status';
    setSelectedStatus(currentStatus);

    // Load notes from a data attribute we'll set on the btn
    const btn = document.getElementById(`notes-btn-${teacherId}`);
    const existingNotes = btn?.dataset.notes ?? '';
    const updatedBy     = btn?.dataset.updatedBy ?? '';
    const updatedAt     = btn?.dataset.updatedAt ?? '';

    document.getElementById('notes-textarea').value = existingNotes;

    const metaEl = document.getElementById('notes-meta');
    metaEl.textContent = updatedAt
        ? `Last updated by ${updatedBy} on ${updatedAt}`
        : '';

    document.getElementById('notes-panel-backdrop').classList.remove('hidden');
}

function closeNotes() {
    document.getElementById('notes-panel-backdrop').classList.add('hidden');
    notesTeacherId  = null;
    notesSemesterId = null;
}

function setSelectedStatus(val) {
    document.querySelectorAll('.status-opt').forEach(opt => {
        opt.classList.toggle('selected', opt.dataset.val === val);
        opt.querySelector('input[type=radio]').checked = (opt.dataset.val === val);
    });
}

// Wire status options
document.querySelectorAll('.status-opt').forEach(opt => {
    opt.addEventListener('click', () => setSelectedStatus(opt.dataset.val));
});

async function saveNote() {
    const status = document.querySelector('.status-opt.selected')?.dataset.val ?? 'no_status';
    const notes  = document.getElementById('notes-textarea').value;

    const btn = document.getElementById('notes-save-btn');
    btn.disabled = true; btn.textContent = 'Saving…';

    try {
        const res = await fetch(`/admin/teachers/${notesTeacherId}/note`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ status, notes, semester_id: notesSemesterId })
        });
        const data = await res.json();

        if (data.success) {
            const badgeEl = document.getElementById(`status-badge-${notesTeacherId}`);
            if (badgeEl) {
                badgeEl.className = `status-badge status-${data.status}`;
                badgeEl.textContent = data.status_label;
            }

            const notesBtn = document.getElementById(`notes-btn-${notesTeacherId}`);
            if (notesBtn) {
                notesBtn.dataset.notes     = data.notes ?? '';
                notesBtn.dataset.updatedBy = data.updated_by ?? '';
                notesBtn.dataset.updatedAt = data.updated_at ?? '';
                notesBtn.classList.toggle('has-note', !!(data.notes));
            }

            closeNotes();
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Save note';
    }
}

// Pre-load existing note data into button data attributes from Blade
// (done inline per teacher block below via a script tag generated in Blade)

// ── Export filtered ───────────────────────────────────────────────────────
function exportFiltered() {
    const params = new URLSearchParams();
    Object.entries(FILTERS).forEach(([k, v]) => { if (v) params.set(k, v); });
    window.location = '/admin/interventions/export?' + params.toString();
}

// ── Print filtered (print current view — filters already applied server-side)
function printFiltered() {
    window.print();
}

// ── Mass delete ───────────────────────────────────────────────────────────
let exportDone = false;

function openMassDelete() {
    exportDone = false;
    document.getElementById('confirm-mass-delete-btn').disabled = true;
    document.getElementById('export-confirmed').classList.add('hidden');

    // Build summary text from filter tags
    const tags  = document.querySelectorAll('.atag');
    const label = tags.length
        ? Array.from(tags).map(t => t.textContent.trim()).join(', ')
        : 'current default semester';
    document.getElementById('mass-delete-summary').textContent =
        `All exam results matching: ${label} will be permanently removed.`;

    document.getElementById('mass-modal').classList.remove('hidden');
}

function closeMassModal() {
    document.getElementById('mass-modal').classList.add('hidden');
}

function exportThenConfirm() {
    exportFiltered();
    exportDone = true;
    document.getElementById('export-confirmed').classList.remove('hidden');
    document.getElementById('confirm-mass-delete-btn').disabled = false;
}

async function confirmMassDelete() {
    const btn = document.getElementById('confirm-mass-delete-btn');
    btn.disabled = true; btn.textContent = 'Deleting…';

    try {
        const res  = await fetch('/admin/interventions/mass-delete', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(FILTERS)
        });
        const data = await res.json();

        if (data.success) {
            closeMassModal();
            // Reload the page to reflect deletion
            window.location.reload();
        } else {
            alert('Delete failed: ' + (data.message ?? 'Unknown error'));
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Yes, delete all';
    }
}

document.getElementById('mass-modal').addEventListener('click', function(e) {
    if (e.target === this) closeMassModal();
});

// ── Cascading filter engine ───────────────────────────────────────────────
(function () {
    const sy      = document.getElementById('f-sy');
    const sem     = document.getElementById('f-sem');
    const dept    = document.getElementById('f-dept');
    const cat     = document.getElementById('f-cat');
    const subj    = document.getElementById('f-subj');
    const teacher = document.getElementById('f-teacher');
    if (!sy) return;

    const allOpts = {};
    [sy, sem, dept, cat, subj, teacher].forEach(sel => {
        allOpts[sel.id] = Array.from(sel.options)
            .filter(o => o.value !== '')
            .map(o => ({ node: o.cloneNode(true), value: o.value, dataset: { ...o.dataset } }));
    });

    function rebuild(select, keepFn) {
        const current = select.value;
        while (select.options.length > 1) select.remove(1);
        let found = false;
        allOpts[select.id].forEach(({ node, value, dataset }) => {
            if (keepFn({ value, dataset })) {
                select.appendChild(node.cloneNode(true));
                if (value === current) found = true;
            }
        });
        select.value = found ? current : '';
    }

    function filterSem()     { rebuild(sem,     ({ dataset }) => !sy.value   || dataset.sy   === sy.value); }
    function filterTeacher() { rebuild(teacher, ({ dataset }) => {
        const ok1 = !sem.value  || (dataset.sem  || '').split(',').includes(sem.value);
        const ok2 = !dept.value || (dataset.dept || '').split(',').includes(dept.value);
        return ok1 && ok2;
    }); }
    function filterSubject() { rebuild(subj, ({ dataset }) =>
        (!dept.value || dataset.dept === dept.value) &&
        (!cat.value  || dataset.cat  === cat.value)
    ); }

    sy.addEventListener('change',   () => { filterSem(); filterTeacher(); });
    sem.addEventListener('change',  () => { filterTeacher(); });
    dept.addEventListener('change', () => { filterSubject(); filterTeacher(); });
    cat.addEventListener('change',  () => { filterSubject(); });

    if (sy.value)               filterSem();
    if (sem.value||dept.value)  filterTeacher();
    if (dept.value||cat.value)  filterSubject();
})();
</script>

{{-- Pre-load existing note data into each notes button --}}
@foreach($grouped as $teacherName => $subjectMap)
@php
    $firstData   = $subjectMap->first();
    $tObj        = $firstData['teacher_subject']->teacher;
    $tNote       = $firstData['teacher_note'];
@endphp
@if($tNote)
<script>
(function() {
    const btn = document.getElementById('notes-btn-{{ $tObj->id }}');
    if (btn) {
        btn.dataset.notes     = {{ json_encode($tNote->notes ?? '') }};
        btn.dataset.updatedBy = {{ json_encode($tNote->updatedByUser?->name ?? '') }};
        btn.dataset.updatedAt = {{ json_encode($tNote->updated_at?->format('M d, Y g:i A') ?? '') }};
    }
})();
</script>
@endif
@endforeach

@endpush