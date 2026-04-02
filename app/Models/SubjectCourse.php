<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectCourse extends Model
{
    protected $table = 'subject_course';

    protected $fillable = ['subject_id', 'course_id', 'department_id'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}