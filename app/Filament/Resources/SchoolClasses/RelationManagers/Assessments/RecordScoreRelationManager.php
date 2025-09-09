<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers\Assessments;

use Filament\Tables\Table;
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
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                TextInputColumn::make('score')
                    ->width('1%')
                    ->placeholder('Max score: ' . ($this->getOwnerRecord()->points ?? 0))
                    ->rules(['numeric', 'min:0', 'max:' . ($this->getOwnerRecord()->points ?? 0)])

            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                ManageSchoolClassStudents::attachAction(),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                ManageSchoolClassStudents::detachBulkAction(),
            ]);
    }
}
