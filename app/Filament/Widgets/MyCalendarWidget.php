<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Filament\Actions\CreateAction;

class MyCalendarWidget extends CalendarWidget
{
    protected bool $dateClickEnabled = true;
    protected bool $eventClickEnabled = true;
    protected bool $dateSelectEnabled = true;


    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return Meeting::query()
            ->whereDate('ends_at', '>=', $info->start)
            ->whereDate('starts_at', '<=', $info->end);
    }

    public function createMeetingAction(): CreateAction
    {
        // You can use our helper method
        return $this->createAction(Meeting::class);
    }

    public function onDateClick(DateClickInfo $info): void
    {
        $this->mountAction('createMeeting');
    }

    protected function getDateClickContextMenuActions(): array
    {
        return [
            $this->createMeetingAction(),
        ];
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return [
            $this->createMeetingAction(),
        ];
    }
}
