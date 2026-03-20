<div class="subject-row-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;margin-bottom:10px;align-items:end">
    <div class="field" style="margin:0">
        <label>Subject</label>
        <select name="subjects[{{ $index }}][subject_id]">
            <option value="">— Select subject —</option>
            @foreach($subjects as $subject)
            <option value="{{ $subject->id }}">
                {{ $subject->subject_code }} — {{ $subject->subject_name }}
                ({{ $subject->department->department_name }})
            </option>
            @endforeach
        </select>
    </div>
    <div class="field" style="margin:0">
        <label>Semester</label>
        <select name="subjects[{{ $index }}][semester_id]">
            <option value="">— Select semester —</option>
            @foreach($semesters as $sem)
            <option value="{{ $sem->id }}">
                {{ $sem->semester_name }} Sem —
                S.Y. {{ $sem->schoolYear->year_start }}–{{ $sem->schoolYear->year_end }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="field" style="margin:0">
        <label>Section</label>
        <input type="text" name="subjects[{{ $index }}][section]" placeholder="e.g. BSIT 1-A">
    </div>
    <div style="padding-bottom:2px">
        <button type="button"
                onclick="this.closest('.subject-row-grid').remove()"
                style="background:none;border:none;cursor:pointer;color:var(--red);font-size:22px;padding:6px 4px;line-height:1">×</button>
    </div>
</div>