<?php

namespace App\Filament\Resources\Meetings\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Concerns\CalendarEventTabs;
use App\Filament\Resources\Meetings\MeetingResource;

class ManageMeetings extends ManageRecords
{
    use CalendarEventTabs;

    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Medium)
        ];
    }
}
