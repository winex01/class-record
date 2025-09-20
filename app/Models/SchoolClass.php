<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use BelongsToUser;

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

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function feeCollections()
    {
        return $this->hasMany(FeeCollection::class);
    }
}
