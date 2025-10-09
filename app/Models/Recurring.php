<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Recurring extends Model
{
    use BelongsToUser;
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'monday' => 'array',
        'tuesday' => 'array',
        'wednesday' => 'array',
        'thursday' => 'array',
        'friday' => 'array',
        'saturday' => 'array',
        'sunday' => 'array',
    ];

    // NOTE:: check MyCalendarWidget
    // public function toCalendarEvent(): CalendarEvent
}
