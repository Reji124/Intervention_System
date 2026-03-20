<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectCategory extends Model
{
    protected $fillable = ['category_name'];
    public function subjects()
{
    return $this->hasMany(Subject::class, 'category_id');
}
}
