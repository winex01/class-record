<?php

namespace App\Filament\Resources\Tasks\Forms;

use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\ToggleButtons;
use App\Filament\Fields\DateTimePicker;
use Filament\Forms\Components\Repeater;

class TaskForm
{
    public static function schema()
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

            Repeater::make('checklists')
                ->schema([
                    Textarea::make('name')
                        ->placeholder('Subtask name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2)
                        ->rows(1),

                    ToggleButtons::make('complete')
                        ->columnSpan(1)
                        ->icons(null)
                ])
                ->defaultItems(0)
                ->columns(3)
        ];
    }
}
