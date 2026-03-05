<?php

namespace App\Filament\Resources\AssessmentTypes\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\AssessmentTypes\AssessmentTypeResource;

class ManageAssessmentTypes extends ManageRecords
{
    protected static string $resource = AssessmentTypeResource::class;
}
