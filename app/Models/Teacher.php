<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'teacher_name',
        'teacher_code',
        'email',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function notes()
    {
        return $this->hasMany(TeacherNote::class);
    }

    /**
     * Get the note for a specific semester (used in intervention view).
     */
    public function noteForSemester(?int $semesterId): ?TeacherNote
    {
        return $this->notes->firstWhere('semester_id', $semesterId);
    }
}