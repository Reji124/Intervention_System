<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'category', 'subject_code', 'year_level', 'subject_name',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'subject_course')
                    ->withPivot('department_id')
                    ->withTimestamps();
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'subject_course')
                    ->withPivot('course_id')
                    ->withTimestamps();
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Filter subjects that belong to a given department via the pivot.
     * Use this instead of ->where('department_id', ...) which doesn't exist
     * as a direct column on the subjects table.
     *
     * Usage:
     *   Subject::forDepartment($deptId)->get();
     *   $query->whereHas('subject', fn($q) => $q->forDepartment($deptId));
     */
    public function scopeForDepartment($query, $departmentId)
    {
        if (!$departmentId) return $query;

        return $query->whereHas('departments', fn($q) =>
            $q->where('departments.id', $departmentId)
        );
    }

    /**
     * Filter subjects by category, trimming whitespace to avoid
     * mismatches from inconsistent DB values.
     */
    public function scopeForCategory($query, $category)
    {
        if (!$category) return $query;

        return $query->whereRaw('LOWER(TRIM(category)) = ?', [
            strtolower(trim($category))
        ]);
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    public function getCourseAttribute()
    {
        return $this->courses->first();
    }

    public function getDepartmentAttribute()
    {
        return $this->departments->first();
    }
}