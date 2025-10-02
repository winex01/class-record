<?php

namespace App\Models;

use Filament\Support\Colors\Color;
use App\Models\Concerns\BelongsToUser;
use Guava\Calendar\Contracts\Eventable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasCalendarRange;
use Guava\Calendar\ValueObjects\CalendarEvent;

class Note extends Model implements Eventable
{
    use BelongsToUser;
    use HasCalendarRange;
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'tags' => 'array',
    ];

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->note)
            ->start($this->starts_at)
            ->end($this->ends_at)
            ->backgroundColor(Color::Amber[500])
            ;
    }
}
