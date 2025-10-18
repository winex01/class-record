<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class Grade extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        //
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
            // ->withPivot(['score', 'group']);
    }
}
