<?php

namespace App\Filament\Resources\Meetings\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Traits\CalendarEventTabFilter;
use App\Filament\Resources\Meetings\MeetingResource;

class ManageMeetings extends ManageRecords
{
    use CalendarEventTabFilter;

    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Meeting')
                ->modalWidth(Width::Medium)
        ];
    }
}
