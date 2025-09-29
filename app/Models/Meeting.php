<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Guava\Calendar\Contracts\Eventable;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\ValueObjects\CalendarEvent;

class Meeting extends Model implements Eventable
{
    use BelongsToUser;
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // This is where you map your model into a calendar object
    public function toCalendarEvent(): CalendarEvent
    {
        // For eloquent models, make sure to pass the model to the constructor
        return CalendarEvent::make($this)
            ->title($this->name)
            ->start($this->starts_at)
            ->end($this->ends_at)
            // ->action('edit')
            ;
    }
}
