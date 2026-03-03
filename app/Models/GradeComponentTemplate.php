<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class GradeComponentTemplate extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'components' => 'array',
    ];
}
