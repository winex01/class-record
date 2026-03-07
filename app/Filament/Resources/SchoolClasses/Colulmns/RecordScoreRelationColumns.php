<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Filament\Columns\SelectColumn;
use App\Filament\Columns\TextInputColumn;
use App\Filament\Resources\Groups\Forms\GroupForm;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class RecordScoreRelationColumns
{
    public static function schema($ownerRecord)
    {
        return [
            ...ManageSchoolClassStudents::getColumns(),

            SelectColumn::make('group')
                ->options(function ($record) {
                    $baseOptions = GroupForm::selectOptions();

                    // Get current value and add it if it doesn't exist
                    $currentValue = $record->pivot->group ?? null;
                        if ($currentValue && !array_key_exists($currentValue, $baseOptions)) {
                            $baseOptions[$currentValue] = $currentValue;
                        }

                    return $baseOptions;
                })
                ->afterStateUpdated(function ($state, $record) {
                    // If the state is null or empty, set it to '-'
                    if (empty($state)) {
                        $record->pivot->group = '-';
                        $record->pivot->save();
                    }
                })
                ->visible($ownerRecord->can_group_students)
                ->disabled(fn () => !$ownerRecord->schoolClass->active),

            TextInputColumn::make('score')
                ->rules(['numeric', 'min:0', 'max:' . ($ownerRecord->max_score ?? 0)])
                ->placeholder(function () use ($ownerRecord) {
                    if (!$ownerRecord->schoolClass->active) {
                        return null;
                    }

                    return 'Max: ' . ($ownerRecord->max_score ?? 0);
                })
                ->disabled(fn () => !$ownerRecord->schoolClass->active),
        ];
    }
}
