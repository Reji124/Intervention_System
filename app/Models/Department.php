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

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}