<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\Meeting;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Contracts\ContextualInfo;
use App\Filament\Resources\Tasks\TaskResource;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use App\Filament\Resources\Meetings\MeetingResource;

class MyCalendarWidget extends CalendarWidget
{
    protected bool $dateClickEnabled = true;
    protected bool $dateSelectEnabled = true;
    protected bool $eventClickEnabled = true;
    protected bool $eventDragEnabled = true;

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return collect()
            ->merge(
                Meeting::withinCalendarRange($info)->get()->map->toCalendarEvent()
            )
            ->merge(
                Task::withinCalendarRange($info)->get()->map->toCalendarEvent()
            );
    }

    // public function getHeaderActions(): array
    // {
    //     return [
    //         $this->createMeetingAction()
    //     ];
    // }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        return $event->update([
            'starts_at' => $info->event->getStart(),
            'ends_at'   => $info->event->getEnd(),
        ]);
    }

    private function defaultCreateAction($model)
    {
        return $this->createAction($model)
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
                        'ends_at'   => $info->end,
                    ]);
                }
            })
            ->modalWidth(Width::Medium);
    }

    private function getActions()
    {
        return [
            $this->defaultCreateAction(Meeting::class),
            $this->defaultCreateAction(Task::class)
                ->modalWidth(Width::Large),
        ];
    }

    protected function getDateClickContextMenuActions(): array
    {
        return $this->getActions();
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return $this->getActions();
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction()
                ->schema(function ($record) {
                    if ($record instanceof Meeting) {
                        return MeetingResource::getForm();
                    }

                    if ($record instanceof Task) {
                        return TaskResource::getForm();
                    }

                    return []; // fallback if neither
                })
                ->modalWidth($this->modalWidth()),

            $this->editAction()->modalWidth($this->modalWidth()),
            $this->deleteAction(),
        ];
    }

    private function modalWidth()
    {
        return function ($record) {
            if ($record instanceof Task) {
                return Width::Large;
            }

            return Width::Medium;
        };
    }

}
