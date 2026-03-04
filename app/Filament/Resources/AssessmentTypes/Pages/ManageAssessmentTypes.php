<?php

namespace App\Filament\Resources\AssessmentTypes\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\AssessmentTypes\AssessmentTypeResource;

class ManageAssessmentTypes extends ManageRecords
{
    protected static string $resource = AssessmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Type')
                ->modalWidth(Width::Medium),
        ];
    }
}
