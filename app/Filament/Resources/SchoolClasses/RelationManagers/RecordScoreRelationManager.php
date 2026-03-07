<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;
use App\Filament\Resources\SchoolClasses\Filters\RecordScoreRelationFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\RecordScoreRelationColumns;

class RecordScoreRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return RecordScoreRelationFilters::getTabs($this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns(RecordScoreRelationColumns::schema($this->getOwnerRecord()))
            ->filters(RecordScoreRelationFilters::filters($this->getOwnerRecord()))
            ->toolbarActions([
                ManageSchoolClassStudents::attachAction($this->getOwnerRecord()),
                ManageSchoolClassStudents::detachBulkAction(),
            ])
            ->defaultGroup($this->getOwnerRecord()->can_group_students ? 'group' : null)
            ->groups(function () {
                if (!$this->getOwnerRecord()->can_group_students) {
                    return [];
                }

                return [
                    Group::make('group')
                        ->titlePrefixedWithLabel(false)
                        ->collapsible(),
                ];
            });

    }
}
