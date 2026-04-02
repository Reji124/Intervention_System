<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['department_name'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Subjects are now linked via subject_course pivot, accessed through courses
    public function subjects()
    {
        return $this->hasManyThrough(
            SubjectCourse::class,
            Course::class,
            'department_id', // FK on courses
            'course_id',     // FK on subject_course
            'id',            // PK on departments
            'id'             // PK on courses
        );
    }
}