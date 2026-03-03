<?php

namespace App\Filament\Resources\GradeComponentTemplates\Pages;

use App\Filament\Resources\GradeComponentTemplates\GradeComponentTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGradeComponentTemplates extends ManageRecords
{
    protected static string $resource = GradeComponentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
