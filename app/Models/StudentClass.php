<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
    ];
}
