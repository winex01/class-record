<?php

namespace App\Filament\Resources\AssessmentTypes\Forms;

use App\Filament\Fields\TextInput;

class AssessmentTypeForm
{
    public static function schema()
    {
        return [
            TextInput::make('name')
                ->required()
        ];
    }
}
