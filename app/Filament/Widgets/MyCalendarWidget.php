<?php

namespace App\Filament\Widgets;

use App\Models\Note;
use App\Models\Task;
use App\Models\Meeting;
use App\Services\Field;
use App\Services\Helper;
use App\Models\Recurring;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Contracts\ContextualInfo;
use App\Filament\Resources\Notes\NoteResource;
use App\Filament\Resources\Tasks\TaskResource;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Resources\Recurrings\RecurringResource;

class MyCalendarWidget extends CalendarWidget
{
    protected bool $dateClickEnabled = true;
    protected bool $dateSelectEnabled = true;
    protected bool $eventClickEnabled = true;
    protected bool $eventDragEnabled = true;
    protected bool $eventResizeEnabled = true;

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    // public function getHeaderActions(): array
    // {
    //     return [
    //         $this->createMeetingAction()
    //     ];
    // }

    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        return collect()
            ->merge(
                Meeting::withinCalendarRange($info)->get()->map->toCalendarEvent()
            )
            ->merge(
                Task::withinCalendarRange($info)->get()->map->toCalendarEvent()
            )
            ->merge(
                Note::withinCalendarRange($info)->get()->map->toCalendarEvent()
            )
            ;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        $calendarEvent = $info->event;
        $newStart = $calendarEvent->getStart();
        $newEnd = $calendarEvent->getEnd();

        if ($newEnd->lessThanOrEqualTo($newStart)) {
            Notification::make()
                ->title('End date must be after start date')
                ->danger()
                ->send();
            return false;
        }

        // Update the event in the database
        $updated = $event->update([
            'starts_at' => $newStart,
            'ends_at' => $newEnd,
        ]);

        if ($updated) {
            $this->updatedSuccessfully();
            return true;
        }

        return false;
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        $updated =$event->update([
            'starts_at' => $info->event->getStart(),
            'ends_at'   => $info->event->getEnd(),
        ]);

        if ($updated) {
            $this->updatedSuccessfully();
            return true;
        }

        return false;
    }

    // TODO:: recurring fields
    private function getActions()
    {
        return [
            $this->defaultCreateAction(Meeting::class),
            $this->defaultCreateAction(Task::class)->modalWidth(Width::Large),
            $this->defaultCreateAction(Note::class),
            $this->defaultCreateAction(Recurring::class)
                ->form([
                    ...RecurringResource::getForm(),

                    Field::date('effectivity_date')
                                ->helperText('Takes effect starting on this date.')
                                ->default(now())->hidden(true),

                    Field::timePicker('monday.starts_at')->hidden(true),
                    // Field::timePicker('monday.ends_at'),
                ])
        ];
    }

    private function defaultCreateAction($model)
    {
        return $this->createAction($model)
            ->mountUsing(function ($form, ?ContextualInfo $info) use ($model) {

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

                    if ($record instanceof Note) {
                        return NoteResource::getForm();
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

    private function updatedSuccessfully()
    {
        return Notification::make()
            ->title('Saved')
            ->success()
            ->send();
    }

}
