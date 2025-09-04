<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyFile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
    ];
}
