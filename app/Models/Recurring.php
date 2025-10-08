<?php

namespace App\Models;

use Filament\Support\Colors\Color;
use App\Models\Concerns\BelongsToUser;
use Guava\Calendar\Contracts\Eventable;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\ValueObjects\CalendarEvent;

class Recurring extends Model implements Eventable
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

    public function toCalendarEvent(): CalendarEvent
    {
        // TODO::
        return CalendarEvent::make($this)
            ->title($this->name)
            ->start($this->created_at)
            ->end($this->updated_at)
            // ->backgroundColor('pink')
            ;
    }
}
