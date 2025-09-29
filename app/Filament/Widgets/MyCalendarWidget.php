<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Guava\Calendar\Enums\Context;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Contracts\ContextualInfo;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\Filament\Actions\CreateAction;

class MyCalendarWidget extends CalendarWidget
{
    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return Meeting::query()
        ->whereDate('ends_at', '>=', $info->start)
        ->whereDate('starts_at', '<=', $info->end);
    }



}
