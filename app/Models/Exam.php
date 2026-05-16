<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    public const GRADING_METHOD_BASE_50 = 'base_50';
    public const GRADING_METHOD_BASE_20 = 'base_20';
    public const GRADING_METHOD_BASE_0 = 'base_0';

    public const GRADING_METHODS = [
        self::GRADING_METHOD_BASE_50,
        self::GRADING_METHOD_BASE_20,
        self::GRADING_METHOD_BASE_0,
    ];

    protected $fillable = [
        'teacher_subject_id',
        'exam_type',
        'grading_method',
        'item_analysis_path',
        'item_matrix_data',
        'uploaded_by',
    ];

    protected $casts = [
        'item_matrix_data' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function computeFinalGrade(int $score, int $total): float
    {
        return self::computeGradeForMethod($this->grading_method, $score, $total);
    }

    public static function computeGradeForMethod(?string $method, int $score, int $total): float
    {
        if ($total <= 0) return 0;

        return match($method ?? self::GRADING_METHOD_BASE_50) {
            self::GRADING_METHOD_BASE_0 => round($score / $total * 100, 2),
            self::GRADING_METHOD_BASE_20 => round(20 + ($score / $total * 80), 2),
            default => round(50 + ($score / $total * 50), 2),
        };
    }

    public static function gradingMethodLabel(?string $method): string
    {
        return match($method ?? self::GRADING_METHOD_BASE_50) {
            self::GRADING_METHOD_BASE_0 => 'Base 0',
            self::GRADING_METHOD_BASE_20 => 'Base 20',
            default => 'Base 50',
        };
    }

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
