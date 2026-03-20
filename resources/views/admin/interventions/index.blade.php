{{-- resources/views/admin/interventions/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Intervention Report')
@section('page-title', 'Intervention Report')

@push('styles')
<style>
.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px; }
.page-header h1 { font-family:'DM Serif Display',serif;font-size:26px;color:var(--text-dark);margin-bottom:4px; }
.page-header p { font-size:13px;color:var(--text-soft); }
.summary-pills { display:flex;gap:10px;align-items:center; }
.spill { display:flex;flex-direction:column;align-items:center;padding:8px 16px;border-radius:10px;min-width:76px; }
.spill-val   { font-family:'DM Serif Display',serif;font-size:22px;line-height:1; }
.spill-label { font-size:10px;text-transform:uppercase;letter-spacing:.6px;margin-top:2px;opacity:.75; }
.spill-fail  { background:var(--red-bg);color:var(--red); }
.spill-pass  { background:var(--green-bg);color:var(--green); }
.btn-print { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:8px;font-size:12px;font-weight:500;border:1.5px solid var(--border);color:var(--text-mid);background:var(--white);cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-print:hover { border-color:var(--text-mid); }
.btn-print svg { width:14px;height:14px; }

/* Filter */
.filter-panel { background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px 24px;margin-bottom:24px; }
.filter-panel-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px; }
.filter-panel-title { font-size:13px;font-weight:600;color:var(--text-dark); }
.filter-panel-sub { font-size:12px;color:var(--text-soft);margin-top:2px; }
.filter-grid { display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px; }
.filter-group { display:flex;flex-direction:column;gap:6px; }
.filter-group label { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft); }
.filter-group select { padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:13px;background:#faf8f5;border:1.5px solid var(--border);border-radius:8px;color:var(--text-dark);outline:none;transition:border-color .2s; }
.filter-group select:focus { border-color:var(--gold);background:var(--white); }
.filter-actions { display:flex;align-items:center;gap:10px;padding-top:16px;border-top:1px solid var(--border); }
.btn-apply { padding:10px 24px;background:var(--navy);color:var(--white);border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:background .15s; }
.btn-apply:hover { background:#1e3050; }
.btn-reset { padding:10px 18px;background:transparent;color:var(--text-mid);border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;cursor:pointer;text-decoration:none;transition:all .15s;display:inline-block; }
.btn-reset:hover { border-color:var(--text-mid); }
.active-tags { display:flex;flex-wrap:wrap;gap:6px;margin-top:12px; }
.atag { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;background:var(--amber-bg);border:1px solid #f0c84a;border-radius:20px;font-size:11px;color:var(--amber);font-weight:500; }

/* Exam list */
.exam-list-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px; }
.exam-list-title { font-family:'DM Serif Display',serif;font-size:18px;color:var(--text-dark); }
.exam-count { font-size:12px;color:var(--text-soft);margin-top:2px; }
.expand-btn { font-size:12px;color:var(--gold);background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:500;padding:0; }
.exam-block { background:var(--white);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:10px;transition:box-shadow .2s;animation:fadeIn .3s ease both; }
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.exam-block:hover { box-shadow:0 2px 14px rgba(0,0,0,.07); }
.exam-header { padding:16px 22px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;transition:background .15s;flex-wrap:wrap;gap:12px;user-select:none; }
.exam-header:hover { background:#faf8f5; }
.exam-left { display:flex;align-items:center;gap:14px; }
.exam-avatar { width:44px;height:44px;background:var(--navy);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'DM Serif Display',serif;font-size:15px;color:#e8b45a;flex-shrink:0; }
.exam-title { font-family:'DM Serif Display',serif;font-size:16px;color:var(--text-dark);display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.exam-meta { font-size:12px;color:var(--text-soft);margin-top:4px; }
.exam-meta span { margin-right:10px; }
.exam-badge { display:inline-block;font-size:10px;font-weight:600;padding:3px 10px;border-radius:20px; }
.eb-prelim{background:var(--amber-bg);color:var(--amber)} .eb-midterm{background:var(--blue-bg);color:var(--blue)} .eb-final{background:#f0ebfa;color:#534ab7}
.matrix-indicator { display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;background:var(--green-bg);color:var(--green);padding:2px 8px;border-radius:20px; }
.matrix-indicator svg { width:10px;height:10px; }
.exam-right { display:flex;align-items:center;gap:12px;flex-wrap:wrap; }
.stat-chips { display:flex;gap:8px; }
.chip { display:flex;flex-direction:column;align-items:center;padding:6px 14px;border-radius:8px;min-width:60px; }
.chip-val   { font-family:'DM Serif Display',serif;font-size:20px;line-height:1; }
.chip-label { font-size:10px;text-transform:uppercase;letter-spacing:.6px;margin-top:2px;opacity:.7; }
.chip-pass{background:var(--green-bg);color:var(--green)} .chip-fail{background:var(--red-bg);color:var(--red)} .chip-rate{background:var(--amber-bg);color:var(--amber)} .chip-total{background:#f0ece3;color:var(--text-mid)}
.toggle-chevron { width:20px;height:20px;color:var(--text-soft);transition:transform .25s;flex-shrink:0; }
.toggle-chevron.open { transform:rotate(180deg); }
.exam-body { border-top:1px solid var(--border);display:none; }
.exam-body.open { display:block; }
.exam-section { border-bottom:1px solid var(--border); }
.exam-section:last-child { border-bottom:none; }
.exam-section-header { padding:12px 22px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:#fafafa;transition:background .15s;user-select:none; }
.exam-section-header:hover { background:#f5f0e8; }
.section-title { font-size:13px;font-weight:600;color:var(--text-dark);display:flex;align-items:center;gap:8px; }
.section-title svg { width:14px;height:14px;color:var(--text-soft); }
.section-chevron { width:16px;height:16px;color:var(--text-soft);transition:transform .2s; }
.exam-section-body { display:none; }
.exam-section-body.open { display:block; }

/* Delete exam button */
.btn-delete-exam { display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-size:11px;font-weight:600;background:var(--red-bg);color:var(--red);border:1px solid #f5c6c6;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-delete-exam:hover { background:#fde8e8;border-color:var(--red); }
.btn-delete-exam svg { width:12px;height:12px; }

/* Master list table */
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

/* Row action buttons */
.row-actions { display:flex;gap:6px;align-items:center; }
.btn-edit-row { display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font-size:11px;font-weight:600;background:#f0f5ff;color:var(--blue);border:1px solid #b5d4f4;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-edit-row:hover { background:#dbeafe; }
.btn-edit-row svg { width:11px;height:11px; }
.btn-del-row { display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font-size:11px;font-weight:600;background:var(--red-bg);color:var(--red);border:1px solid #f5c6c6;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s; }
.btn-del-row:hover { background:#fde8e8; }
.btn-del-row svg { width:11px;height:11px; }

/* Matrix */
.matrix-layout { display:grid;grid-template-columns:1fr 260px;gap:20px;padding:20px 22px;align-items:start; }
.matrix-section-title { font-size:12px;font-weight:600;color:var(--text-dark);margin-bottom:10px;display:flex;align-items:center;gap:6px; }
.matrix-section-title svg { width:14px;height:14px;color:var(--gold); }
.matrix-grid-wrap { overflow-x:auto; }
table.matrix-tbl { width:100%;border-collapse:collapse;min-width:600px; }
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
.matrix-legend-row { display:flex;gap:20px;flex-wrap:wrap;padding:10px 22px;border-top:1px solid var(--border);background:#fdfcfa; }
.legend-item { display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text-mid); }
.legend-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
.legend-dot.reject{background:#c0392b} .legend-dot.needs-revision{background:#856404} .legend-dot.acceptable{background:#1a6e34}
.legend-count { font-weight:700;margin-left:2px; }
.legend-count.reject{color:#c0392b} .legend-count.needs-revision{color:#856404} .legend-count.acceptable{color:#1a6e34}
.summary-card { background:#faf8f5;border:1px solid var(--border);border-radius:10px;overflow:hidden; }
.summary-card-header { padding:10px 14px;background:var(--navy);font-size:11px;font-weight:600;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.7px; }
.summary-row { display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--border); }
.summary-row:last-child { border-bottom:none; }
.summary-diff { display:flex;align-items:center;gap:8px; }
.summary-diff-label { font-size:12px;font-weight:500;color:var(--text-dark); }
.summary-diff-sub   { font-size:10px;color:var(--text-soft);margin-top:1px; }
.summary-count { font-family:'DM Serif Display',serif;font-size:20px;color:var(--text-dark); }
.summary-bar-wrap { margin-top:4px;height:3px;background:var(--border);border-radius:2px; }
.summary-bar-fill { height:100%;border-radius:2px; }
.pdf-link { display:block;padding:12px 22px;font-size:12px;color:var(--blue);text-decoration:none;border-top:1px solid var(--border);display:flex;align-items:center;gap:5px; }
.pdf-link svg { width:13px;height:13px; }
.pdf-link:hover { background:#f0f5ff; }
.empty-state { text-align:center;padding:60px;background:var(--white);border:1px solid var(--border);border-radius:12px; }
.empty-state h3 { font-family:'DM Serif Display',serif;font-size:20px;color:var(--text-mid);margin-bottom:8px; }
.empty-state p { font-size:13px;color:var(--text-soft); }

/* ── Edit modal ──────────────────────────────────────────────────────── */
.modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;display:flex;align-items:center;justify-content:center;padding:20px; }
.modal-backdrop.hidden { display:none; }
.modal { background:var(--white);border-radius:14px;width:100%;max-width:420px;overflow:hidden;animation:modalIn .2s ease both; }
@keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
.modal-header { padding:18px 22px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
.modal-title { font-family:'DM Serif Display',serif;font-size:17px;color:var(--text-dark); }
.modal-close { width:28px;height:28px;border-radius:50%;border:none;background:#f0ece3;color:var(--text-mid);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;line-height:1;font-family:'DM Sans',sans-serif; }
.modal-close:hover { background:var(--border); }
.modal-body { padding:20px 22px;display:flex;flex-direction:column;gap:14px; }
.modal-field { display:flex;flex-direction:column;gap:5px; }
.modal-field label { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--text-soft); }
.modal-field input { padding:9px 12px;font-family:'DM Sans',sans-serif;font-size:13px;background:#faf8f5;border:1.5px solid var(--border);border-radius:8px;color:var(--text-dark);outline:none;transition:border-color .2s; }
.modal-field input:focus { border-color:var(--teal-light);background:var(--white); }
.modal-preview { display:flex;gap:10px;padding:10px 14px;background:#f0faf7;border:1px solid #9fe1cb;border-radius:8px;font-size:12px;color:var(--teal); }
.modal-preview span { font-weight:600; }
.modal-footer { padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end; }
.btn-modal-cancel { padding:9px 18px;background:transparent;color:var(--text-mid);border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;cursor:pointer; }
.btn-modal-save { padding:9px 20px;background:var(--navy);color:var(--white);border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:background .15s; }
.btn-modal-save:hover { background:#1e3050; }
.btn-modal-save:disabled { opacity:.6;cursor:not-allowed; }

@media print {
    .filter-panel,.btn-print,.expand-btn,.row-actions,.btn-delete-exam { display:none !important; }
    .exam-body,.exam-section-body { display:block !important; }
    .sidebar,.topbar { display:none !important; }
    .main { margin-left:0 !important; }
}
</style>
@endpush

@section('content')

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
        <button class="btn-print" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
    </div>
</div>

{{-- Filter --}}
<div class="filter-panel">
    <div class="filter-panel-header">
        <div>
            <div class="filter-panel-title">Filter results</div>
            <div class="filter-panel-sub">Defaults to the current semester.</div>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.interventions.index') }}">
        <input type="hidden" name="_filtered" value="1">
        <div class="filter-grid">
            <div class="filter-group">
                <label>School year</label>
                <select name="school_year_id">
                    <option value="">All school years</option>
                    @foreach($schoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ $selectedSY == $sy->id ? 'selected' : '' }}>S.Y. {{ $sy->year_start }}–{{ $sy->year_end }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Semester</label>
                <select name="semester_id">
                    <option value="">All semesters</option>
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSem == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }} Sem — S.Y. {{ $sem->schoolYear->year_start }}–{{ $sem->schoolYear->year_end }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Department</label>
                <select name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $selectedDept == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
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
                <select name="subject_id">
                    <option value="">All subjects</option>
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ $selectedSubject == $subj->id ? 'selected' : '' }}>{{ $subj->subject_code }} — {{ $subj->subject_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Teacher</label>
                <select name="teacher_id">
                    <option value="">All teachers</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ $selectedTeacher == $teacher->id ? 'selected' : '' }}>{{ $teacher->teacher_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-apply">Apply filters</button>
            <a href="{{ route('admin.interventions.index') }}" class="btn-reset">Reset to default</a>
        </div>
        @php $hasFilters = $selectedSY || $selectedDept || $selectedCat || $selectedSubject || $selectedTeacher || ($selectedSem && $selectedSem != $activeSemester?->id); @endphp
        @if($hasFilters)
        <div class="active-tags">
            @if($selectedSem)  @php $sem  = $semesters->find($selectedSem);      @endphp @if($sem)  <span class="atag">{{ $sem->semester_name }} Sem S.Y. {{ $sem->schoolYear->year_start }}–{{ $sem->schoolYear->year_end }}</span>@endif @endif
            @if($selectedDept) @php $dept = $departments->find($selectedDept);    @endphp @if($dept) <span class="atag">{{ $dept->department_name }}</span>@endif @endif
            @if($selectedCat)  @php $cat  = $categories->find($selectedCat);      @endphp @if($cat)  <span class="atag">{{ $cat->category_name }}</span>@endif @endif
            @if($selectedSubject) @php $subj = $subjects->find($selectedSubject); @endphp @if($subj) <span class="atag">{{ $subj->subject_code }}</span>@endif @endif
            @if($selectedTeacher) @php $tchr = $teachers->find($selectedTeacher); @endphp @if($tchr) <span class="atag">{{ $tchr->teacher_name }}</span>@endif @endif
        </div>
        @else
        <div class="active-tags">
            <span class="atag" style="background:var(--green-bg);border-color:#b7dfc5;color:var(--green)">Showing: {{ $activeSemester?->semester_name }} Sem S.Y. {{ $activeSemester?->schoolYear?->year_start }}–{{ $activeSemester?->schoolYear?->year_end }} (default)</span>
        </div>
        @endif
    </form>
</div>

{{-- Exam list --}}
<div class="exam-list-header">
    <div>
        <div class="exam-list-title">Exam results</div>
        <div class="exam-count">{{ $exams->count() }} exam(s) found</div>
    </div>
    @if($exams->count())
    <button class="expand-btn" id="expand-all-btn" onclick="expandAll()">Expand all</button>
    @endif
</div>

@forelse($exams as $exam)
@php
    $ts        = $exam->teacherSubject;
    $subj      = $ts->subject;
    $tchr      = $ts->teacher;
    $sem       = $ts->semester;
    $inits     = collect(explode(' ', $tchr->teacher_name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
    $hasMatrix = !empty($exam->item_matrix_data);
    $matrix    = $exam->item_matrix_data ?? [];
    $discCols  = $matrix['disc_columns']  ?? [];
    $matrixRows= $matrix['rows']          ?? [];
    $colTotals = $matrix['column_totals'] ?? [];
    $grandTotal= $matrix['grand_total']   ?? 0;
    $legend    = $matrix['legend']        ?? [];
    $diffColors= ['81-100%'=>'#27ae60','61-80%'=>'#2ecc71','41-60%'=>'#f39c12','21-40%'=>'#e67e22','0-20%'=>'#e74c3c'];
    $chipClass = function(string $col): string {
        if (in_array($col,['<.00','.00-.14'])) return 'chip-reject';
        if (in_array($col,['.15-.24','.25-.29'])) return 'chip-needs-revision';
        return 'chip-acceptable';
    };
@endphp

<div class="exam-block" id="exam-block-{{ $exam->id }}">
    <div class="exam-header" onclick="toggleExam(this)">
        <div class="exam-left">
            <div class="exam-avatar">{{ $inits }}</div>
            <div>
                <div class="exam-title">
                    {{ $tchr->teacher_name }}
                    <span class="exam-badge eb-{{ $exam->exam_type }}">{{ ucfirst($exam->exam_type) }}</span>
                    @if($hasMatrix)<span class="matrix-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Matrix</span>@endif
                </div>
                <div class="exam-meta">
                    <span>{{ $subj->subject_code }} — {{ $subj->subject_name }}</span>
                    <span>{{ $ts->section }}</span>
                    <span>{{ $sem->semester_name }} Sem, S.Y. {{ $sem->schoolYear->year_start }}–{{ $sem->schoolYear->year_end }}</span>
                </div>
            </div>
        </div>
        <div class="exam-right">
            <div class="stat-chips">
                <div class="chip chip-total"><span class="chip-val" id="total-{{ $exam->id }}">{{ $exam->total_students }}</span><span class="chip-label">Total</span></div>
                <div class="chip chip-pass"><span class="chip-val" id="pass-{{ $exam->id }}">{{ $exam->pass_count }}</span><span class="chip-label">Passed</span></div>
                <div class="chip chip-fail"><span class="chip-val" id="fail-{{ $exam->id }}">{{ $exam->fail_count }}</span><span class="chip-label">Failed</span></div>
                <div class="chip chip-rate"><span class="chip-val" id="rate-{{ $exam->id }}">{{ $exam->pass_rate }}%</span><span class="chip-label">Pass rate</span></div>
            </div>
            {{-- Delete exam button (stops propagation so it doesn't toggle) --}}
            <button class="btn-delete-exam" onclick="event.stopPropagation();deleteExam({{ $exam->id }})">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Delete exam
            </button>
            <svg class="toggle-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
    </div>

    <div class="exam-body">
        {{-- Master list --}}
        <div class="exam-section">
            <div class="exam-section-header" onclick="toggleSection(this)">
                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Master list
                    <span style="font-size:11px;color:var(--text-soft);font-weight:400">— <span id="count-{{ $exam->id }}">{{ $exam->total_students }}</span> students</span>
                </div>
                <svg class="section-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="exam-section-body">
                @if($exam->examResults->count())
                <table class="master-tbl">
                    <thead>
                        <tr>
                            <th>#</th><th>Student name</th><th>Code</th>
                            <th>Raw score [T]</th><th>Percentage</th><th>Remark</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-{{ $exam->id }}">
                        @foreach($exam->examResults->sortBy('student.student_name') as $i => $result)
                        <tr id="row-{{ $result->id }}">
                            <td style="font-size:11px;color:var(--text-soft)">{{ $i + 1 }}</td>
                            <td><div class="td-name">{{ $result->student->student_name }}</div></td>
                            <td style="font-size:12px;color:var(--text-soft)">{{ $result->student->student_code }}</td>
                            <td id="score-{{ $result->id }}">{{ $result->raw_score }}</td>
                            <td><span id="pct-{{ $result->id }}" class="{{ $result->remark === 'fail' ? 'pct-fail' : 'pct-pass' }}">{{ $result->percentage }}%</span></td>
                            <td><span id="badge-{{ $result->id }}" class="badge badge-{{ $result->remark }}">{{ ucfirst($result->remark) }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-edit-row" onclick="openEdit({{ $result->id }}, {{ $result->raw_score }}, {{ $exam->id }})">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <button class="btn-del-row" onclick="deleteResult({{ $result->id }}, {{ $exam->id }})">
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
                <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-soft)">No student results recorded yet.</div>
                @endif
            </div>
        </div>

        {{-- Item Analysis Matrix --}}
        <div class="exam-section">
            <div class="exam-section-header" onclick="toggleSection(this)">
                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                    Item analysis matrix
                    @if(!$hasMatrix)<span style="font-size:11px;color:var(--text-soft);font-weight:400">(not uploaded)</span>@endif
                </div>
                <svg class="section-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="exam-section-body">
                @if($hasMatrix)
                <div class="matrix-layout">
                    <div>
                        <div class="matrix-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                            Difficulty × Discrimination grid
                            <span style="font-size:11px;color:var(--text-soft);font-weight:400">— {{ $grandTotal }} items</span>
                        </div>
                        <div class="matrix-grid-wrap">
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
                                            <span style="font-size:10px;color:var(--text-soft);font-weight:400;margin-left:2px">{{ $row['label'] }}</span>
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
                                        <td class="row-total">{{ $row['total'] }}</td>
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
                            <div class="legend-item"><span class="legend-dot reject"></span>Reject (&lt;.00 – .00-.14): <span class="legend-count reject">{{ count($legend['reject'] ?? []) }}</span></div>
                            <div class="legend-item"><span class="legend-dot needs-revision"></span>Needs Revision: <span class="legend-count needs-revision">{{ count($legend['needs_revision'] ?? []) }}</span></div>
                            <div class="legend-item"><span class="legend-dot acceptable"></span>Acceptable: <span class="legend-count acceptable">{{ count($legend['acceptable'] ?? []) }}</span></div>
                        </div>
                    </div>
                    <div>
                        <div class="matrix-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            Difficulty summary
                        </div>
                        <div class="summary-card">
                            <div class="summary-card-header">By difficulty level</div>
                            @foreach($matrixRows as $row)
                            @php $color = $diffColors[$row['difficulty']] ?? '#888'; $pct = $grandTotal > 0 ? round(($row['total'] / $grandTotal) * 100) : 0; @endphp
                            <div class="summary-row">
                                <div class="summary-diff">
                                    <span class="diff-dot" style="background:{{ $color }}"></span>
                                    <div><div class="summary-diff-label">{{ $row['difficulty'] }}</div><div class="summary-diff-sub">{{ $row['label'] }}</div></div>
                                </div>
                                <div style="text-align:right"><div class="summary-count">{{ $row['total'] }}</div><div style="font-size:10px;color:var(--text-soft)">{{ $pct }}%</div></div>
                            </div>
                            <div style="padding:0 14px 8px"><div class="summary-bar-wrap"><div class="summary-bar-fill" style="width:{{ $pct }}%;background:{{ $color }}"></div></div></div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($exam->item_analysis_path)
                <a href="{{ asset('storage/' . $exam->item_analysis_path) }}" target="_blank" class="pdf-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    View original Item Analysis PDF
                </a>
                @endif
                @else
                <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-soft)">No item analysis matrix uploaded for this exam.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="empty-state"><h3>No exams found</h3><p>No exams match your current filters.</p></div>
@endforelse

{{-- Edit modal --}}
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

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let editingResultId = null;
let editingExamId   = null;

// ── Toggle helpers ────────────────────────────────────────────────────────────
function toggleExam(header) {
    const body = header.nextElementSibling;
    const chev = header.querySelector('.toggle-chevron');
    body.classList.toggle('open');
    chev.classList.toggle('open');
}
function toggleSection(header) {
    const body = header.nextElementSibling;
    const chev = header.querySelector('.section-chevron');
    body.classList.toggle('open');
    chev.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : '';
}
function expandAll() {
    const btn     = document.getElementById('expand-all-btn');
    const bodies  = document.querySelectorAll('.exam-body');
    const chevs   = document.querySelectorAll('.toggle-chevron');
    const anyOpen = document.querySelector('.exam-body.open');
    bodies.forEach(b => b.classList.toggle('open', !anyOpen));
    chevs.forEach(c  => c.classList.toggle('open', !anyOpen));
    btn.textContent = anyOpen ? 'Expand all' : 'Collapse all';
}

// ── Edit modal ────────────────────────────────────────────────────────────────
function openEdit(resultId, rawScore, examId) {
    editingResultId = resultId;
    editingExamId   = examId;
    document.getElementById('edit-raw').value   = rawScore;
    document.getElementById('edit-total').value = '';
    document.getElementById('edit-preview').style.display = 'none';
    document.getElementById('edit-modal').classList.remove('hidden');
    document.getElementById('edit-raw').focus();
}
function closeEdit() {
    document.getElementById('edit-modal').classList.add('hidden');
    editingResultId = null;
    editingExamId   = null;
}
// Live preview
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
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const res  = await fetch(`/admin/exam-results/${editingResultId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ raw_score: raw, total })
        });
        const data = await res.json();
        if (data.success) {
            // Update row in place
            document.getElementById(`score-${editingResultId}`).textContent = data.raw_score;
            const pctEl = document.getElementById(`pct-${editingResultId}`);
            pctEl.textContent = data.percentage + '%';
            pctEl.className = data.remark === 'fail' ? 'pct-fail' : 'pct-pass';
            const badgeEl = document.getElementById(`badge-${editingResultId}`);
            badgeEl.textContent = data.remark.charAt(0).toUpperCase() + data.remark.slice(1);
            badgeEl.className = `badge badge-${data.remark}`;
            // Refresh summary chips
            refreshExamChips(editingExamId);
            closeEdit();
        }
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save changes';
    }
}

// ── Delete result ─────────────────────────────────────────────────────────────
async function deleteResult(resultId, examId) {
    if (!confirm('Delete this student result? This cannot be undone.')) return;
    const res  = await fetch(`/admin/exam-results/${resultId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById(`row-${resultId}`)?.remove();
        refreshExamChips(examId);
    }
}

// ── Delete entire exam ────────────────────────────────────────────────────────
async function deleteExam(examId) {
    if (!confirm('Delete this entire exam and ALL student results? This cannot be undone.')) return;
    const res  = await fetch(`/admin/exams/${examId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById(`exam-block-${examId}`)?.remove();
    }
}

// ── Refresh pass/fail/total chips after edit/delete ───────────────────────────
function refreshExamChips(examId) {
    const tbody = document.getElementById(`tbody-${examId}`);
    if (!tbody) return;
    const rows   = tbody.querySelectorAll('tr');
    let pass = 0, fail = 0;
    rows.forEach(row => {
        const badge = row.querySelector('[id^="badge-"]');
        if (!badge) return;
        if (badge.classList.contains('badge-pass')) pass++;
        else fail++;
    });
    const total = pass + fail;
    const rate  = total > 0 ? Math.round((pass / total) * 100) : 0;
    const set   = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set(`total-${examId}`, total);
    set(`pass-${examId}`,  pass);
    set(`fail-${examId}`,  fail);
    set(`rate-${examId}`,  rate + '%');
    set(`count-${examId}`, total);
}

// Close modal on backdrop click
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>
@endpush