<?php

namespace App\Filament\Widgets;

use App\Models\Note;
use App\Models\Task;
use App\Models\Meeting;
use App\Services\Field;
use App\Services\Helper;
use Carbon\CarbonPeriod;
use App\Models\Recurring;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Contracts\ContextualInfo;
use App\Filament\Resources\Notes\NoteResource;
use App\Filament\Resources\Tasks\TaskResource;
use Guava\Calendar\ValueObjects\CalendarEvent;
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
        return [
            ...Meeting::withinCalendarRange($info)->get()->map->toCalendarEvent(),
            ...Task::withinCalendarRange($info)->get()->map->toCalendarEvent(),
            ...Note::withinCalendarRange($info)->get()->map->toCalendarEvent(),
            ...$this->recurringEvents($info),
        ];
    }

    // TODO::
    public function recurringEvents($info)
    {
        $events = [];

        foreach (Recurring::get() as $item) {
            $period = CarbonPeriod::create($item->date_start, $item->date_end);

            $weekDays = [];
            foreach ($period as $date) {
                if ($date->lessThan($info->start) || $date->greaterThan($info->end)) {
                    continue;
                }

                $day = Helper::getDayName($date);

                if (! in_array($day, $weekDays)) {
                    $weekDays[] = $day;
                }
            }

            dd($weekDays);

            $events[] = CalendarEvent::make()
                ->model(Recurring::class)
                ->key($item->getKey())
                ->title($item->name)
                ->start(now())
                ->end(now()->addHours(2))
                ->backgroundColor(Color::Pink[500]);
        }

        return $events;
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

    private function getActions()
    {
        return [
            $this->defaultCreateAction(Meeting::class),
            $this->defaultCreateAction(Task::class)->modalWidth(Width::Large),
            $this->defaultCreateAction(Note::class),
            $this->recurringCreateAction(Recurring::class),
        ];
    }

    public function defaultCreateAction($model)
    {
        return $this->createAction($model)
            ->mountUsing(function ($form, ?ContextualInfo $info)  {
                if ($info instanceof DateClickInfo) {
                    $form->fill([
                        'starts_at' => $info->date->startOfDay(),
                        'ends_at'   => $info->date->endOfDay(),
                    ]);
                }

                if ($info instanceof DateSelectInfo) {
                    $form->fill([
                        'starts_at' => $info->start->startOfDay(),
                        'ends_at'   => $info->end->endOfDay(),
                    ]);
                }
            })
            ->modalWidth(Width::Medium);
    }

    public function recurringCreateAction($model)
    {
        return $this->defaultCreateAction($model)
            ->mountUsing(function ($form, ?ContextualInfo $info)  {
                if ($info instanceof DateClickInfo) {
                    $form->fill([
                        'date_start' => $info->date->startOfDay(),
                        'date_end' => $info->date->endOfDay(),
                        strtolower($info->date->dayName) => [['starts_at' => now()->startOfDay(), 'ends_at' => now()->endOfDay()]],
                    ]);
                }

                if ($info instanceof DateSelectInfo) {
                    $period = CarbonPeriod::create($info->start, $info->end->subDay());

                    $clickedDays = collect($period)
                        ->map(fn ($date) => strtolower($date->dayName))
                        ->unique()   // keep only unique weekdays
                        ->values()
                        ->toArray();

                    $form->fill([
                        'date_start' => $info->start->startOfDay(),
                        'date_end' => $info->start->endOfCentury(),
                        ...collect($clickedDays)
                            ->mapWithKeys(fn ($day) => [
                                $day => [
                                    [
                                        'starts_at' => now()->startOfDay(),
                                        'ends_at'   => now()->endOfDay(),
                                    ]
                                ]
                            ])
                            ->toArray()
                    ]);
                }
            });
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

    public function modalWidth()
    {
        return function ($record) {
            if ($record instanceof Task) {
                return Width::Large;
            }

            return Width::Medium;
        };
    }

    public function updatedSuccessfully()
    {
        return Notification::make()
            ->title('Saved')
            ->success()
            ->send();
    }

}
