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
            ->orderByRaw("CASE semester_name
                WHEN '1st Semester' THEN 1
                WHEN '2nd Semester' THEN 2
                WHEN 'Summer'       THEN 3
                ELSE 4
            END");
    }
}