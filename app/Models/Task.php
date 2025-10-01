<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Guava\Calendar\Contracts\Eventable;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\ValueObjects\CalendarEvent;

class Task extends Model implements Eventable
{
    use BelongsToUser;
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'tags' => 'array',
        'checklists' => 'array',
    ];

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->name)
            ->start($this->starts_at)
            ->end($this->ends_at);
    }
}
