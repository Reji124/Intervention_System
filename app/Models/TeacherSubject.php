<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    protected $fillable = ['teacher_id', 'subject_id', 'semester_id', 'section'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // ExamResult → through Exam → foreign key on exams is teacher_subject_id
    public function examResults()
    {
        return $this->hasManyThrough(
            ExamResult::class,  // final model
            Exam::class,        // intermediate model
            'teacher_subject_id', // FK on exams pointing to teacher_subjects
            'exam_id',            // FK on exam_results pointing to exams
            'id',                 // local key on teacher_subjects
            'id'                  // local key on exams
        );
    }
}