<?php

namespace App\Filament\Resources\SchoolClasses\Forms;

use App\Models\MyFile;
use App\Enums\LessonStatus;
use App\Models\SchoolClass;
use App\Filament\Fields\Select;
use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater\TableColumn;

class SchoolClassLessonForm
{
    public static function schema(SchoolClass $ownerRecord)
    {
        return [
            Hidden::make('school_class_id')->default($ownerRecord->id),
            Hidden::make('status')
            ->default(function ($livewire) {
                if (!empty($livewire->mountedActions)) {
                    $firstAction = $livewire->mountedActions[0];
                    if (isset($firstAction['arguments']['column'])) {
                        $column = $firstAction['arguments']['column'];
                        return LessonStatus::tryFrom($column)?->value ?? LessonStatus::TOPICS->value;
                    }
                }
                return LessonStatus::TOPICS->value;
            }),

            Grid::make(2)
            ->schema([
                Section::make()
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    TagsInput::make('tags'),
                    DatePicker::make('completion_date'),
                    Repeater::make('checklist')
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
                ])
                ->columnSpan(1), // end Section 1

                Section::make()
                ->schema([
                    Textarea::make('description')
                        ->rows(5)
                        ->columnSpanFull(),
                    Select::make('myFiles')
                        ->multiple()
                        ->preload(false)
                        ->options(MyFile::pluck('name', 'id'))
                        ->dehydrated(false)
                        ->saveRelationshipsUsing(function ($component, $state, $record) {
                            $record->myFiles()->sync($state ?? []);
                        })
                        ->loadStateFromRelationshipsUsing(function ($component, $record) {
                            $component->state($record->myFiles->pluck('id')->toArray());
                        }),
                ])->columnSpan(1), // end Section 2
            ]), // end Grid
        ];
    }
}
