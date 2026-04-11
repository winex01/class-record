<?php

namespace App\Filament\Resources\AssessmentTypes\Schemas;

use App\Filament\Fields\TextInput;

class AssessmentTypeForm
{
    public static function getFields()
    {
        return [
            TextInput::make('name')
                ->required()
        ];
    }
}
