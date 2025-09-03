<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),
                ToggleColumn::make('present')
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
