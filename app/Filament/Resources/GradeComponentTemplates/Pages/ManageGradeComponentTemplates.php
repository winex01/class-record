<?php

namespace App\Filament\Resources\GradeComponentTemplates\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\GradeComponentTemplates\GradeComponentTemplateResource;

class ManageGradeComponentTemplates extends ManageRecords
{
    protected static string $resource = GradeComponentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::ExtraLarge),
        ];
    }
}
