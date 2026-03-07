<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassStudentActions;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;
use App\Filament\Resources\SchoolClasses\Actions\TakeAttendanceRelationActions;
use App\Filament\Resources\SchoolClasses\Filters\TakeAttendanceRelationFilters;

class TakeAttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return TakeAttendanceRelationFilters::getTabs($this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...SchoolClassStudentColumns::schema(),

                ToggleColumn::make('present')
                    ->offColor('danger')
                    ->width('1%')
                    ->alignCenter()
                    ->sortable()
                    ->disabled(fn () => !$this->getOwnerRecord()->schoolClass->active),
            ])
            ->filters([
                StudentFilters::gender(),
            ])
            ->toolbarActions([
                SchoolClassStudentActions::attachAction($this->getOwnerRecord()),
                TakeAttendanceRelationActions::bulkMarkAbsentAction(),
                TakeAttendanceRelationActions::bulkMarkPresentAction(),
                SchoolClassStudentActions::detachBulkAction(),
            ]);
    }
}
