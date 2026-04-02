<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'category', 'subject_code', 'year_level', 'subject_name',
    ];

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

    // Convenience: first course assigned (for legacy single-display use)
    public function getCourseAttribute()
    {
        return $this->courses->first();
    }

    // Convenience: first department assigned (for legacy single-display use)
    public function getDepartmentAttribute()
    {
        return $this->departments->first();
    }
}