<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchoolClass;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

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
}
