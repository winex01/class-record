<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DateTimePicker;

class MeetingForm
{
    public static function getFields()
    {
        return [
            TextInput::make('name')
                    ->required()
                    ->maxLength(255),

            Textarea::make('description')
                ->placeholder('Optional...'),

            TagsInput::make('tags'),

            DateTimePicker::make('starts_at')
                ->default(now()->startOfDay())
                ->beforeOrEqual('ends_at')
                ->required(),

            DateTimePicker::make('ends_at')
                ->default(now()->endOfDay())
                ->afterOrEqual('starts_at')
                ->required(),
        ];
    }
}
