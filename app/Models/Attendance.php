<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class Attendance extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps()
            ->withPivot(['present']);
    }

   public function getDateFormattedAttribute()
    {
        return $this->date->format('M d, Y');
    }
}
