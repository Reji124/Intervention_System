<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = ['year_start', 'year_end'];

    // Used by the dashboard filter dropdown: "SY 2025–2026"
    public function getYearLabelAttribute(): string
    {
        return "SY {$this->year_start}–{$this->year_end}";
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class)
            ->orderByRaw("FIELD(semester_name, '1st Semester', '2nd Semester', 'Summer')");
    }
}