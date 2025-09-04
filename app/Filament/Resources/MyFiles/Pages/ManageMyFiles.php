<?php

namespace App\Filament\Resources\MyFiles\Pages;

use App\Filament\Resources\MyFiles\MyFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMyFiles extends ManageRecords
{
    protected static string $resource = MyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
