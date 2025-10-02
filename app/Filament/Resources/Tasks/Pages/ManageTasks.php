<?php

namespace App\Filament\Resources\Tasks\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Concerns\CalendarEventTabs;
use App\Filament\Resources\Tasks\TaskResource;

class ManageTasks extends ManageRecords
{
    use CalendarEventTabs;

    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Large)
        ];
    }
}
