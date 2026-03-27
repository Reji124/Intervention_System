<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherNote extends Model
{
    protected $fillable = [
        'teacher_id',
        'semester_id',
        'status',
        'notes',
        'updated_by',
    ];

    // Status options
    const STATUSES = [
        'no_status'           => 'No Status',
        'on_track'            => 'On Track',
        'needs_followup'      => 'Needs Follow-up',
        'intervention_active' => 'Intervention Active',
        'resolved'            => 'Resolved',
    ];

    // CSS classes for each status badge (matches blade variables)
    const STATUS_STYLES = [
        'no_status'           => ['bg' => '#f0ece3', 'color' => '#8a7f72'],
        'on_track'            => ['bg' => 'var(--green-bg)', 'color' => 'var(--green)'],
        'needs_followup'      => ['bg' => 'var(--amber-bg)', 'color' => 'var(--amber)'],
        'intervention_active' => ['bg' => 'var(--red-bg)', 'color' => 'var(--red)'],
        'resolved'            => ['bg' => '#e8f0fe', 'color' => '#1a56db'],
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'No Status';
    }
}