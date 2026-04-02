<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'category', 'subject_code', 'year_level', 'subject_name',
    ];

    /**
     * All course+department assignments for this subject.
     * Pivot carries department_id so you can read it without extra queries.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'subject_course')
                    ->withPivot('department_id')
                    ->withTimestamps();
    }

    /**
     * Convenience: all departments this subject belongs to (via pivot).
     */
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
}