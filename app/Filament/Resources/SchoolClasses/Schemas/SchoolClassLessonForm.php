<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use App\Models\MyFile;
use App\Enums\LessonStatus;
use App\Models\SchoolClass;
use App\Filament\Fields\Select;
use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use App\Filament\Actions\ClearAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;

class SchoolClassLessonForm
{
    public static function getFields(SchoolClass $ownerRecord)
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

            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('description')->rows(3),
            TagsInput::make('tags'),

            DatePicker::make('completion_date'),
            Select::make('myFiles')
                ->hint('Attach related files')
                ->multiple()
                ->preload(false)
                ->options(MyFile::pluck('name', 'id'))
                ->dehydrated(false)
                ->saveRelationshipsUsing(function ($component, $state, $record) {
                    $record->myFiles()->sync($state ?? []);
                })
                ->loadStateFromRelationshipsUsing(function ($component, $record) {
                    $component->state($record->myFiles->pluck('id')->toArray());
                })
                ->suffixAction(ClearAction::make()),

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
