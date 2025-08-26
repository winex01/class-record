<?php

namespace App\Filament\Resources\StudentClasses\Pages;

use App\Filament\Resources\StudentClasses\StudentClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudentClasses extends ManageRecords
{
    protected static string $resource = StudentClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
