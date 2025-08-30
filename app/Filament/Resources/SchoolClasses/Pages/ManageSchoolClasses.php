<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClasses extends ManageRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
