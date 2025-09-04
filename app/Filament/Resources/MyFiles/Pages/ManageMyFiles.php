<?php

namespace App\Filament\Resources\MyFiles\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\MyFiles\MyFileResource;

class ManageMyFiles extends ManageRecords
{
    protected static string $resource = MyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New upload')
                ->modalWidth(Width::Medium)
        ];
    }
}
