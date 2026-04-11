<?php

namespace App\Filament\Resources\Notes\Schemas;

use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\DateTimePicker;

class NoteForm
{
    public static function getFields()
    {
        return [
            Textarea::make('note')
                ->required(),

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
