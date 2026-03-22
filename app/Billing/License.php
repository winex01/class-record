<?php

namespace App\Billing;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'date',
    ];
}
