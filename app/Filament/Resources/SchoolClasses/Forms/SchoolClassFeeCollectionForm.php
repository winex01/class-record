<?php

namespace App\Filament\Resources\SchoolClasses\Forms;

use App\Filament\Fields\Textarea;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use App\Filament\Fields\NumericInput;

class SchoolClassFeeCollectionForm
{
    public static function schema()
    {
        return [
            TextInput::make('name')
                ->required(),

            NumericInput::make('amount')
                ->helperText('Enter 0 for voluntary contribution')
                ->default(0)
                ->required()
                ->minValue(0),

            DatePicker::make('date'),

            Textarea::make('description')
                ->placeholder('Additional details...')
        ];
    }
}
