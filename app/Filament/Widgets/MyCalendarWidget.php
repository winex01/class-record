<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Models\Meeting;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Contracts\ContextualInfo;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\Filament\Actions\CreateAction;

class MyCalendarWidget extends CalendarWidget
{
    protected bool $dateClickEnabled = true;
    protected bool $dateSelectEnabled = true;
    protected bool $eventClickEnabled = true;
    protected bool $eventDragEnabled = true;

    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return collect()
            ->push(...Meeting::query()->get())
            ;
    }

    // public function getHeaderActions(): array
    // {
    //     return [
    //         $this->createMeetingAction()
    //     ];
    // }

    public function createMeetingAction(): CreateAction
    {
        return $this->createAction(Meeting::class)
            ->mountUsing(function ($form, ?ContextualInfo $info) {
                if ($info instanceof DateClickInfo) {
                    $form->fill([
                        'starts_at' => $info->date->startOfDay(),
                        'ends_at'   => $info->date->endOfDay(),
                    ]);
                }

                if ($info instanceof DateSelectInfo) {
                    $form->fill([
                        'starts_at' => $info->start,
                        'ends_at'   => $info->end->subDay(),
                    ]);
                }
            })
            ->modalWidth(Width::Medium);
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        return $event->update([
            'starts_at' => $info->event->getStart(),
            'ends_at'   => $info->event->getEnd(),
        ]);
    }

    // public function onDateClick(DateClickInfo $info): void
    // {
    //     // $this->mountAction('createMeeting');
    //     // $th
    // }

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

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction()->schema(MeetingResource::getForm())->modalWidth(Width::Medium),
            $this->editAction()->modalWidth(Width::Medium),
            $this->deleteAction(),
        ];
    }
}
