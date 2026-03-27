<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'teacher_subject_id',
        'exam_type',
        'item_analysis_path',
        'item_matrix_data',
        'uploaded_by',
    ];

    protected $casts = [
        'item_matrix_data' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, ExamResult::class);
    }

    public function teacherSubject()
    {
        return $this->belongsTo(TeacherSubject::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    public function getTotalStudentsAttribute(): int
    {
        return $this->examResults->count();
    }

    public function getPassCountAttribute(): int
    {
        return $this->examResults->where('remark', 'pass')->count();
    }

    public function getFailCountAttribute(): int
    {
        return $this->examResults->where('remark', 'fail')->count();
    }

    public function getPassRateAttribute(): int
    {
        $total = $this->total_students;
        return $total > 0 ? (int) round(($this->pass_count / $total) * 100) : 0;
    }
}