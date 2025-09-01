<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
