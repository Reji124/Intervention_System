<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
protected $fillable = ['teacher_subject_id', 'student_name', 'student_code'];
public function teacherSubject()
{
    return $this->belongsTo(TeacherSubject::class);
}

public function examResults()
{
    return $this->hasMany(ExamResult::class);
}
}
