<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers\Assessments;

use App\Models\Group;
use Filament\Tables\Table;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class RecordScoreRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort('full_name', 'asc')
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                SelectColumn::make('group')
                    ->placeholder('-')
                    ->options(function ($record) {
                        $baseOptions = Group::all()->pluck('name', 'name')
                                ->prepend('-', '-')
                                ->toArray();

                        // Get current value and add it if it doesn't exist
                        $currentValue = $record->pivot->group ?? null;
                        if ($currentValue && !array_key_exists($currentValue, $baseOptions)) {
                            $baseOptions[$currentValue] = $currentValue;
                        }

                        return $baseOptions;
                    })
                    ->disablePlaceholderSelection()
                    ->afterStateUpdated(function ($state, $record) {
                        // If the state is null or empty, set it to '-'
                        if (empty($state)) {
                            $record->pivot->group = '-';
                            $record->pivot->save();
                        }
                    })
                    ->native(false)
                    ->visible($this->getOwnerRecord()->can_group_students),

                TextInputColumn::make('score')
                    ->width('1%')
                    ->sortable()
                    ->placeholder('Max score: ' . ($this->getOwnerRecord()->max_score ?? 0))
                    ->rules(['numeric', 'min:0', 'max:' . ($this->getOwnerRecord()->max_score ?? 0)])

            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                ManageSchoolClassStudents::attachAction($this->getOwnerRecord()),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                ManageSchoolClassStudents::detachBulkAction(),
            ]);
    }
}
