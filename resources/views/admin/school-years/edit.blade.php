@extends('layouts.admin')
@section('title', 'Edit ' . $schoolYear->year_label)
@section('page-title', 'Edit ' . $schoolYear->year_label)

@section('content')

<style>
    .form-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 28px 32px;
        max-width: 560px;
    }

    .field { margin-bottom: 20px; }
    .field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-mid);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .field input[type="number"] {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-dark);
        background: var(--card-bg);
        outline: none;
        transition: border-color .15s;
    }
    .field input[type="number"]:focus { border-color: var(--navy); }
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .field-error { font-size: 12px; color: var(--red); margin-top: 4px; }
    .req { color: var(--red); }

    /* ── Semester checkboxes ─────────────────────────────────────────────────── */
    .sem-check-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 6px;
    }

    .sem-check-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        user-select: none;
    }

    .sem-check-item:has(input:checked) {
        border-color: var(--navy);
        background: #f0f4ff;
    }

    .sem-check-item-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sem-check-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--navy);
        cursor: pointer;
        flex-shrink: 0;
    }

    .sem-check-label {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
    }

    /* Status pill on the right */
    .sem-status {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 9px;
        border-radius: 20px;
    }
    .sem-status-exists  { background: var(--green-bg); color: var(--green); }
    .sem-status-new     { background: var(--amber-bg); color: var(--amber); }
    .sem-status-remove  { background: var(--red-bg);   color: var(--red);   }
    .sem-status-none    { background: #f0f0f0;          color: #999;         }

    /* ── Warning box shown when unchecking an existing semester ─────────────── */
    .danger-notice {
        display: none;
        margin-top: 14px;
        padding: 12px 14px;
        background: var(--red-bg);
        border: 1px solid #ffc9c9;
        border-radius: 8px;
        font-size: 12px;
        color: var(--red);
        line-height: 1.6;
    }
    .danger-notice strong { font-weight: 700; }

    /* ── Form actions ────────────────────────────────────────────────────────── */
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
</style>

{{-- back link --}}
<a href="{{ route('admin.school-years.show', $schoolYear) }}"
   style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-soft);text-decoration:none;font-weight:500;margin-bottom:20px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    Back to {{ $schoolYear->year_label }}
</a>

<div class="form-card">
    <form method="POST" action="{{ route('admin.school-years.update', $schoolYear) }}">
    @csrf
    @method('PUT')

    {{-- Year range --}}
    <div class="field-row">
        <div class="field">
            <label>Year start <span class="req">*</span></label>
            <input type="number" name="year_start"
                value="{{ old('year_start', $schoolYear->year_start) }}"
                min="2000" max="2100" required>
            @error('year_start')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Year end <span class="req">*</span></label>
            <input type="number" name="year_end"
                value="{{ old('year_end', $schoolYear->year_end) }}"
                min="2001" max="2101" required>
            @error('year_end')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Semesters --}}
    @php
        $allSemesters    = ['1st Semester', '2nd Semester', 'Summer'];
        $existingNames   = $schoolYear->semesters->pluck('semester_name')->toArray();
        // On validation failure, restore what the user had checked
        $checkedNames    = old('semesters', $existingNames);
    @endphp

    <div class="field">
        <label>Semesters</label>
        <div class="sem-check-group" id="sem-group">
            @foreach($allSemesters as $sem)
            @php
                $isExisting = in_array($sem, $existingNames);
                $isChecked  = in_array($sem, $checkedNames);
            @endphp
            <label class="sem-check-item">
                <div class="sem-check-item-left">
                    <input type="checkbox"
                        name="semesters[]"
                        value="{{ $sem }}"
                        {{ $isChecked ? 'checked' : '' }}
                        data-existing="{{ $isExisting ? 'true' : 'false' }}"
                        onchange="onSemChange()">
                    <span class="sem-check-label">{{ $sem }}</span>
                </div>
                {{-- status pill updated by JS --}}
                <span class="sem-status {{ $isChecked ? ($isExisting ? 'sem-status-exists' : 'sem-status-new') : ($isExisting ? 'sem-status-remove' : 'sem-status-none') }}"
                    data-sem="{{ $sem }}">
                    {{ $isChecked ? ($isExisting ? 'Saved' : 'Will be added') : ($isExisting ? 'Will be removed' : 'Not included') }}
                </span>
            </label>
            @endforeach
        </div>

        {{-- Warning shown when an existing semester is about to be removed --}}
        <div class="danger-notice" id="danger-notice">
            <strong>Warning:</strong> Removing a semester will also delete all subjects, exams,
            and exam results linked to it. This cannot be undone.
        </div>

        @error('semesters')<p class="field-error" style="margin-top:8px">{{ $message }}</p>@enderror
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.school-years.show', $schoolYear) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>

    </form>
</div>

@push('scripts')
<script>
    const labels = {
        checked_existing:   { cls: 'sem-status-exists',  text: 'Saved'            },
        checked_new:        { cls: 'sem-status-new',     text: 'Will be added'    },
        unchecked_existing: { cls: 'sem-status-remove',  text: 'Will be removed'  },
        unchecked_none:     { cls: 'sem-status-none',    text: 'Not included'     },
    };

    function onSemChange() {
        let hasRemoval = false;

        document.querySelectorAll('#sem-group input[type="checkbox"]').forEach(cb => {
            const isExisting = cb.dataset.existing === 'true';
            const isChecked  = cb.checked;
            const pill       = document.querySelector(`.sem-status[data-sem="${cb.value}"]`);

            // Determine state key
            let key;
            if (isChecked  &&  isExisting) key = 'checked_existing';
            if (isChecked  && !isExisting) key = 'checked_new';
            if (!isChecked &&  isExisting) { key = 'unchecked_existing'; hasRemoval = true; }
            if (!isChecked && !isExisting) key = 'unchecked_none';

            // Swap pill class and text
            pill.className = 'sem-status ' + labels[key].cls;
            pill.textContent = labels[key].text;
        });

        document.getElementById('danger-notice').style.display = hasRemoval ? 'block' : 'none';
    }
</script>
@endpush

@endsection