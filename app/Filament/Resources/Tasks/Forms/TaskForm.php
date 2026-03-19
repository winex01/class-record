<?php

namespace App\Filament\Resources\Tasks\Forms;

use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use Filament\Forms\Components\Toggle;
use App\Filament\Fields\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;

class TaskForm
{
    public static function schema()
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->rows(3)
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
                ->table([
                    TableColumn::make('Item'),
                    TableColumn::make('Done')->width(1),
                ])
                ->schema([
                    TextInput::make('item')->placeholder('Enter checklist item'),
                    Toggle::make('done')->default(false)
                ])
                ->compact()
                ->minItems(0)
                ->defaultItems(0),
        ];
    }
}
