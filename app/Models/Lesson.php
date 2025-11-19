<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class Lesson extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'checklist' => 'array',
    ];
}
