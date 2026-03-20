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
    select option { font-size:13px; padding:6px; }
    .teacher-reveal { display:flex; align-items:center; gap:10px; padding:12px 14px; background:#f0faf7; border:1px solid #9fe1cb; border-radius:8px; font-size:13px; color:var(--teal); margin-top:8px; transition:all .3s; overflow:hidden; }
    .teacher-reveal svg { width:16px; height:16px; flex-shrink:0; }
    .teacher-reveal.hidden { opacity:0; height:0; padding:0; margin:0; border:none; }
    .teacher-name-display { font-weight:600; }
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
    .btn-secondary { background:transparent; color:var(--text-mid); border:1.5px solid var(--border); }
    .info-note { display:flex; gap:10px; padding:12px 14px; background:var(--blue-bg); border:1px solid #b5d4f4; border-radius:8px; font-size:12px; color:var(--blue); line-height:1.6; }
    .info-note svg { flex-shrink:0; width:15px; height:15px; margin-top:1px; }
    .field-error { font-size:11px; color:var(--red); margin-top:2px; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('assistant.upload.parse') }}" enctype="multipart/form-data">
@csrf
<div class="upload-card">
    <div class="upload-card-header">
        <h2>Upload exam results</h2>
        <p>Select the subject and exam type. The teacher will be identified automatically.</p>
    </div>
    <div class="upload-body">

        {{-- Step 1 --}}
        <div>
            <div class="step-label">Step 1 — Select subject & exam type</div>
            <div class="field-row" style="margin-bottom:8px">
                <div class="field">
                    <label for="teacher_subject_id">Subject & Section <span class="req">*</span></label>
                    <select name="teacher_subject_id" id="teacher_subject_id" required>
                        <option value="">— Select subject —</option>
                        @forelse($teacherSubjects as $ts)
                            <option value="{{ $ts->id }}"
                                    data-teacher="{{ $ts->teacher->teacher_name ?? 'Unknown' }}"
                                    {{ old('teacher_subject_id') == $ts->id ? 'selected' : '' }}>
                                {{ $ts->subject->subject_code }} — {{ $ts->subject->subject_name }}
                                | {{ $ts->section }}
                                | {{ $ts->semester->semester_name }} Sem
                                S.Y. {{ $ts->semester->schoolYear->year_start }}–{{ $ts->semester->schoolYear->year_end }}
                            </option>
                        @empty
                            <option value="" disabled>No subjects found — ask admin to assign subjects to teachers first</option>
                        @endforelse
                    </select>
                    @error('teacher_subject_id')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="exam_type">Exam type <span class="req">*</span></label>
                    <select name="exam_type" id="exam_type" required>
                        <option value="">— Select type —</option>
                        <option value="prelim"  {{ old('exam_type') == 'prelim'  ? 'selected' : '' }}>Prelim</option>
                        <option value="midterm" {{ old('exam_type') == 'midterm' ? 'selected' : '' }}>Midterm</option>
                        <option value="prefinal"  {{ old('exam_type') == 'prefinal'  ? 'selected' : '' }}>Prefinal</option>
                        <option value="final"   {{ old('exam_type') == 'final'   ? 'selected' : '' }}>Final</option>
                    </select>
                    @error('exam_type')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="teacher-reveal hidden" id="teacher-reveal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Teacher: <span class="teacher-name-display" id="teacher-name-display"></span></span>
            </div>
        </div>

        <hr class="divider">

        {{-- Step 2 --}}
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
                        <span class="field-label-sub">— reference only</span>
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
            <span>Students scoring <strong>75% and above</strong> = Pass. Rows with missing name or code will be flagged for manual input before saving.</span>
        </div>
    </div>

    <div class="upload-footer">
        <a href="{{ route('assistant.dashboard') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Parse PDF
        </button>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
const select  = document.getElementById('teacher_subject_id');
const reveal  = document.getElementById('teacher-reveal');
const display = document.getElementById('teacher-name-display');

function updateTeacher() {
    const opt     = select.options[select.selectedIndex];
    const teacher = opt ? opt.getAttribute('data-teacher') : null;
    if (teacher && select.value) {
        display.textContent = teacher;
        reveal.classList.remove('hidden');
    } else {
        reveal.classList.add('hidden');
    }
}
select.addEventListener('change', updateTeacher);
updateTeacher();

function wireFile(inputId, areaId, labelId) {
    const input = document.getElementById(inputId);
    const area  = document.getElementById(areaId);
    const label = document.getElementById(labelId);
    input.addEventListener('change', () => {
        if (input.files[0]) { label.textContent = input.files[0].name; area.classList.add('has-file'); }
    });
    area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('dragover'); });
    area.addEventListener('dragleave', () => area.classList.remove('dragover'));
    area.addEventListener('drop', e => {
        e.preventDefault(); area.classList.remove('dragover');
        if (e.dataTransfer.files[0]) {
            input.files = e.dataTransfer.files;
            label.textContent = e.dataTransfer.files[0].name;
            area.classList.add('has-file');
        }
    });
}
wireFile('master-input', 'master-area', 'master-label');
wireFile('matrix-input', 'matrix-area', 'matrix-label');
</script>
@endpush