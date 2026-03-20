<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
   protected $fillable = ['year_start', 'year_end'];

   public function semesters()
{
    return $this->hasMany(Semester::class);
}
}
