<?php

namespace App\Filament\Resources\Tasks\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Tasks\TaskResource;

class ManageTasks extends ManageRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Large)
        ];
    }
}
