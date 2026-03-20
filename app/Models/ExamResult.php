<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = ['student_id', 'exam_id', 'raw_score', 'percentage', 'remark'];
    public function student()
{
    return $this->belongsTo(Student::class);
}

public function exam()
{
    return $this->belongsTo(Exam::class);
}
}
