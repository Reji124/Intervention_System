{{-- resources/views/assistant/upload/index.blade.php --}}
@extends('layouts.assistant')
@section('title', 'Upload Exam Results')
@section('page-title', 'Upload Exam Results')

@push('styles')
<style>
    .upload-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; max-width:700px; animation:slideUp .4s ease both; }
    @keyframes slideUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    .upload-card-header { padding:22px 28px 18px; border-bottom:1px solid var(--border); }
    .upload-card-header h2 { font-family:'DM Serif Display',serif; font-size:20px; color:var(--text-dark); margin-bottom:4px; }
    .upload-card-header p { font-size:13px; color:var(--text-soft); }
    .upload-body { padding:24px 28px; display:flex; flex-direction:column; gap:20px; }
    .step-label { font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--border); }
    .field { display:flex; flex-direction:column; gap:6px; }
    .field-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    label { font-size:12px; font-weight:500; color:var(--text-dark); }
    label .req { color:var(--red); }
    select, input[type="text"] {
        width:100%; padding:10px 12px; font-family:'DM Sans',sans-serif; font-size:13px;
        background:#faf8f5; border:1.5px solid var(--border); border-radius:8px;
        color:var(--text-dark); outline:none; transition:border-color .2s, box-shadow .2s;
        appearance:auto;
    }
    select:focus, input:focus { border-color:var(--teal-light); background:var(--white); box-shadow:0 0 0 3px rgba(29,158,117,.1); }
    select:disabled { opacity:.5; cursor:not-allowed; background:#f0ece3; }
    select option { font-size:13px; padding:6px; }
    .context-reveal { display:flex; align-items:center; gap:10px; padding:12px 14px; background:#f0faf7; border:1px solid #9fe1cb; border-radius:8px; font-size:13px; color:var(--teal); margin-top:4px; transition:all .3s; }
    .context-reveal.hidden { display:none; }
    .context-reveal svg { width:16px; height:16px; flex-shrink:0; }
    .context-reveal strong { font-weight:600; }
    .ts-warning { display:none; margin-top:6px; padding:10px 14px; background:var(--red-bg,#fff0f0); border:1px solid #f5c6c6; border-radius:8px; font-size:12px; color:var(--red,#c0392b); }
    .divider { border:none; border-top:1px solid var(--border); }
    .upload-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .file-upload-area { border:2px dashed var(--border); border-radius:10px; padding:24px; display:flex; flex-direction:column; align-items:center; gap:8px; cursor:pointer; transition:all .2s; text-align:center; position:relative; background:#faf8f5; }
    .file-upload-area:hover, .file-upload-area.dragover { border-color:var(--teal-light); background:#f0faf7; }
    .file-upload-area svg { width:32px; height:32px; color:var(--border); transition:color .2s; }
    .file-upload-area.has-file svg { color:var(--teal-light); }
    .file-upload-area input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
    .file-upload-label { font-size:13px; font-weight:500; color:var(--text-mid); }
    .file-upload-sub { font-size:11px; color:var(--text-soft); }
    .field-label-block { font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:8px; }
    .field-label-sub { font-size:11px; color:var(--text-soft); font-weight:400; margin-left:4px; }
    .upload-footer { padding:18px 28px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
    .btn { display:inline-flex; align-items:center; gap:7px; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; border:none; cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif; }
    .btn-primary { background:var(--navy); color:var(--white); }
    .btn-primary:hover { background:#1e3050; }
    .btn-primary:disabled { opacity:.5; cursor:not-allowed; }
    .btn-secondary { background:transparent; color:var(--text-mid); border:1.5px solid var(--border); }
    .info-note { display:flex; gap:10px; padding:12px 14px; background:var(--blue-bg); border:1px solid #b5d4f4; border-radius:8px; font-size:12px; color:var(--blue); line-height:1.6; }
    .info-note svg { flex-shrink:0; width:15px; height:15px; margin-top:1px; }
    .field-error { font-size:11px; color:var(--red); margin-top:2px; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('assistant.upload.parse') }}" enctype="multipart/form-data" id="upload-form">
@csrf

{{-- Resolved by JS before submit --}}
<input type="hidden" name="teacher_subject_id" id="resolved-ts-id">

<div class="upload-card">
    <div class="upload-card-header">
        <h2>Upload exam results</h2>
        <p>Select the school year, semester, subject, exam type, and teacher to identify the class.</p>
    </div>
    <div class="upload-body">

        {{-- ── Step 1 ─────────────────────────────────────────────────────── --}}
        <div>
            <div class="step-label">Step 1 — Identify the class</div>

            {{-- Row 1: School year + Semester --}}
            <div class="field-row" style="margin-bottom:16px">
                <div class="field">
                    <label for="sel-sy">School Year <span class="req">*</span></label>
                    <select id="sel-sy">
                        <option value="">— Select school year —</option>
                        @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}">
                            S.Y. {{ $sy->year_start }}–{{ $sy->year_end }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="sel-sem">Semester <span class="req">*</span></label>
                    <select id="sel-sem" disabled>
                        <option value="">— Select semester —</option>
                        @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" data-sy="{{ $sem->school_year_id }}">
                            {{ $sem->semester_name }} Sem
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2: Subject + Exam type --}}
            <div class="field-row" style="margin-bottom:16px">
                <div class="field">
                    <label for="sel-subject">Subject <span class="req">*</span></label>
                    <select id="sel-subject" disabled>
                        <option value="">— Select subject —</option>
                        @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">
                            {{ $subj->subject_code }} — {{ $subj->subject_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="sel-exam-type">Exam Type <span class="req">*</span></label>
                    <select id="sel-exam-type" name="exam_type" disabled>
                        <option value="">— Select exam type —</option>
                        <option value="prelim"   {{ old('exam_type') == 'prelim'   ? 'selected' : '' }}>Prelim</option>
                        <option value="midterm"  {{ old('exam_type') == 'midterm'  ? 'selected' : '' }}>Midterm</option>
                        <option value="prefinal" {{ old('exam_type') == 'prefinal' ? 'selected' : '' }}>Pre-Final</option>
                        <option value="final"    {{ old('exam_type') == 'final'    ? 'selected' : '' }}>Final</option>
                    </select>
                    @error('exam_type')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Row 3: Teacher (full width) --}}
            <div class="field" style="margin-bottom:8px">
                <label for="sel-teacher">Teacher <span class="req">*</span></label>
                <select id="sel-teacher" disabled>
                    <option value="">— Select teacher —</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">
                        {{ $teacher->teacher_code ? $teacher->teacher_code . ' — ' : '' }}{{ $teacher->teacher_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            @error('teacher_subject_id')
            <p class="field-error">{{ $message }}</p>
            @enderror

            {{-- Warning: no matching class found --}}
            <div class="ts-warning" id="ts-warning">
                No class found for this combination. Make sure this subject, semester, and teacher are set up by the admin.
            </div>

            {{-- Confirm: resolved class context --}}
            <div class="context-reveal hidden" id="context-reveal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Class identified: <strong id="context-text"></strong></span>
            </div>
        </div>

        <hr class="divider">

        {{-- ── Step 2 ─────────────────────────────────────────────────────── --}}
        <div>
            <div class="step-label">Step 2 — Upload PDFs</div>
            <div class="upload-grid">
                <div class="field">
                    <div class="field-label-block">
                        Master List PDF <span style="color:var(--red)">*</span>
                        <span class="field-label-sub">— names + scores</span>
                    </div>
                    <div class="file-upload-area" id="master-area">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        <span class="file-upload-label" id="master-label">Click or drag to upload</span>
                        <span class="file-upload-sub">PDF only</span>
                        <input type="file" name="master_list" accept=".pdf" id="master-input" required>
                    </div>
                    @error('master_list')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <div class="field-label-block">
                        Item Analysis PDF
                        <span class="field-label-sub">— optional</span>
                    </div>
                    <div class="file-upload-area" id="matrix-area">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span class="file-upload-label" id="matrix-label">Click or drag to upload</span>
                        <span class="file-upload-sub">PDF only (optional)</span>
                        <input type="file" name="item_matrix" accept=".pdf" id="matrix-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="info-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Students scoring <strong>75% and above</strong> = Pass. Rows with missing name or code will be flagged for manual input before saving. Name mismatches with existing records will also be highlighted for review.</span>
        </div>

    </div>

    <div class="upload-footer">
        <a href="{{ route('assistant.dashboard') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Parse PDF
        </button>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
// ── All teacher_subjects for client-side resolution ───────────────────────
const TS_DATA = @json($teacherSubjects->map(fn($ts) => [
    'id'          => $ts->id,
    'semester_id' => $ts->semester_id,
    'subject_id'  => $ts->subject_id,
    'teacher_id'  => $ts->teacher->id,
    'teacher_name'=> $ts->teacher->teacher_name,
    'subject_code'=> $ts->subject->subject_code,
    'subject_name'=> $ts->subject->subject_name,
    'section'     => $ts->section,
    'semester_name'=> $ts->semester->semester_name,
]));

const selSY       = document.getElementById('sel-sy');
const selSem      = document.getElementById('sel-sem');
const selSubject  = document.getElementById('sel-subject');
const selExamType = document.getElementById('sel-exam-type');
const selTeacher  = document.getElementById('sel-teacher');
const resolvedId  = document.getElementById('resolved-ts-id');
const warning     = document.getElementById('ts-warning');
const contextReveal = document.getElementById('context-reveal');
const contextText   = document.getElementById('context-text');
const submitBtn     = document.getElementById('submit-btn');

// Snapshot all options once for re-filtering
const semOpts     = Array.from(selSem.options).slice(1).map(o => o.cloneNode(true));
const subjectOpts = Array.from(selSubject.options).slice(1).map(o => o.cloneNode(true));
const teacherOpts = Array.from(selTeacher.options).slice(1).map(o => o.cloneNode(true));

// ── Helpers ───────────────────────────────────────────────────────────────
function resetSelect(sel, placeholder) {
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    sel.disabled  = true;
    sel.value     = '';
}

function resolve() {
    const semId     = selSem.value;
    const subjectId = selSubject.value;
    const teacherId = selTeacher.value;
    const examType  = selExamType.value;

    const match = TS_DATA.find(ts =>
        String(ts.semester_id) === semId &&
        String(ts.subject_id)  === subjectId &&
        String(ts.teacher_id)  === teacherId
    );

    resolvedId.value = match ? match.id : '';

    const allChosen = semId && subjectId && teacherId;
    warning.style.display = allChosen && !match ? 'block' : 'none';

    const fullyReady = match && examType;
    submitBtn.disabled = !fullyReady;

    if (fullyReady) {
        contextText.textContent =
            `${match.subject_code} — ${match.subject_name} | ${match.section} | `+
            `${match.semester_name} Sem | ${match.teacher_name} | `+
            `${selExamType.options[selExamType.selectedIndex].text}`;
        contextReveal.classList.remove('hidden');
    } else {
        contextReveal.classList.add('hidden');
    }
}

// ── Cascade: School Year → Semester ──────────────────────────────────────
selSY.addEventListener('change', function () {
    const syId = this.value;
    selSem.innerHTML = '<option value="">— Select semester —</option>';
    semOpts.forEach(o => {
        if (!syId || o.dataset.sy === syId) selSem.appendChild(o.cloneNode(true));
    });
    selSem.disabled = !syId;
    resetSelect(selSubject, '— Select subject —');
    resetSelect(selTeacher, '— Select teacher —');
    selExamType.disabled = true;
    selExamType.value    = '';
    resolve();
});

// ── Cascade: Semester → Subject ───────────────────────────────────────────
selSem.addEventListener('change', function () {
    const semId = this.value;
    const validSubjectIds = new Set(
        TS_DATA.filter(ts => !semId || String(ts.semester_id) === semId)
               .map(ts => String(ts.subject_id))
    );
    selSubject.innerHTML = '<option value="">— Select subject —</option>';
    subjectOpts.forEach(o => {
        if (validSubjectIds.has(o.value)) selSubject.appendChild(o.cloneNode(true));
    });
    selSubject.disabled = !semId || validSubjectIds.size === 0;
    resetSelect(selTeacher, '— Select teacher —');
    selExamType.disabled = true;
    selExamType.value    = '';
    resolve();
});

// ── Cascade: Subject → Teacher ────────────────────────────────────────────
selSubject.addEventListener('change', function () {
    const semId     = selSem.value;
    const subjectId = this.value;
    const validTeacherIds = new Set(
        TS_DATA.filter(ts =>
            (!semId     || String(ts.semester_id) === semId) &&
            (!subjectId || String(ts.subject_id)  === subjectId)
        ).map(ts => String(ts.teacher_id))
    );
    selTeacher.innerHTML = '<option value="">— Select teacher —</option>';
    teacherOpts.forEach(o => {
        if (validTeacherIds.has(o.value)) selTeacher.appendChild(o.cloneNode(true));
    });
    selTeacher.disabled = !subjectId || validTeacherIds.size === 0;
    selExamType.disabled = true;
    selExamType.value    = '';
    resolve();
});

// ── Teacher selected → enable exam type ───────────────────────────────────
selTeacher.addEventListener('change', function () {
    const semId     = selSem.value;
    const subjectId = selSubject.value;
    const teacherId = this.value;
    const hasMatch  = TS_DATA.some(ts =>
        String(ts.semester_id) === semId &&
        String(ts.subject_id)  === subjectId &&
        String(ts.teacher_id)  === teacherId
    );
    selExamType.disabled = !hasMatch;
    if (!hasMatch) selExamType.value = '';
    resolve();
});

// ── Exam type change → final resolve ─────────────────────────────────────
selExamType.addEventListener('change', resolve);

// ── Auto-select active semester on load ───────────────────────────────────
@if($activeSemester)
(function () {
    const activeSyId  = '{{ $activeSemester->school_year_id }}';
    const activeSemId = '{{ $activeSemester->id }}';
    selSY.value = activeSyId;
    // Trigger SY cascade first
    selSY.dispatchEvent(new Event('change'));
    // Then set semester after DOM settles
    setTimeout(() => {
        selSem.value = activeSemId;
        selSem.dispatchEvent(new Event('change'));
    }, 0);
})();
@endif

// ── File upload UI ────────────────────────────────────────────────────────
function wireFile(inputId, areaId, labelId) {
    const input = document.getElementById(inputId);
    const area  = document.getElementById(areaId);
    const label = document.getElementById(labelId);
    input.addEventListener('change', () => {
        if (input.files[0]) {
            label.textContent = input.files[0].name;
            area.classList.add('has-file');
        }
    });
    area.addEventListener('dragover',  e => { e.preventDefault(); area.classList.add('dragover'); });
    area.addEventListener('dragleave', () => area.classList.remove('dragover'));
    area.addEventListener('drop', e => {
        e.preventDefault();
        area.classList.remove('dragover');
        if (e.dataTransfer.files[0]) {
            input.files         = e.dataTransfer.files;
            label.textContent   = e.dataTransfer.files[0].name;
            area.classList.add('has-file');
        }
    });
}
wireFile('master-input', 'master-area', 'master-label');
wireFile('matrix-input', 'matrix-area', 'matrix-label');

// ── Submit guard ──────────────────────────────────────────────────────────
document.getElementById('upload-form').addEventListener('submit', function (e) {
    if (!resolvedId.value) {
        e.preventDefault();
        warning.style.display   = 'block';
        warning.textContent     = 'Please complete all fields before uploading.';
        selSY.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endpush