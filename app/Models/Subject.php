<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
    'department_id', 'course_id', 'category', 'subject_code',
    'year_level', 'subject_name',
];
    public function department()
{
    return $this->belongsTo(Department::class);
}
public function course()
{
    return $this->belongsTo(Course::class);
}



public function teacherSubjects()
{
    return $this->hasMany(TeacherSubject::class);
}
}
