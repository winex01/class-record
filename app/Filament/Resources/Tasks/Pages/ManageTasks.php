<?php

namespace App\Filament\Resources\Tasks\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Tasks\TaskResource;
use App\Filament\Traits\CalendarEventTabFilter;

class ManageTasks extends ManageRecords
{
    use CalendarEventTabFilter;

    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Task')
                ->modalWidth(Width::Large)
        ];
    }
}
