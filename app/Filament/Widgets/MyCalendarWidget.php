<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;

class MyCalendarWidget extends CalendarWidget
{
    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return [
            \Guava\Calendar\ValueObjects\CalendarEvent::make()
            ->title('My first calendar')
            ->start(now())
            ->end(now()->addHours(2)),
        ];
    }

}
