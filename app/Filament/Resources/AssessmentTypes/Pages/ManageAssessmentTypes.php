<?php

namespace App\Filament\Resources\AssessmentTypes\Pages;

use App\Filament\Resources\AssessmentTypes\AssessmentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageAssessmentTypes extends ManageRecords
{
    protected static string $resource = AssessmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Medium),
        ];
    }
}
