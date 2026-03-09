<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use Filament\Tables\Table;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\Actions\TakeFeeCollectionRelationActions;
use App\Filament\Resources\SchoolClasses\Filters\TakeFeeCollectionRelationFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\TakeFeeCollectionRelationColumns;

class TakeFeeCollectionRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return TakeFeeCollectionRelationFilters::getTabs($this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns(TakeFeeCollectionRelationColumns::schema($this->getOwnerRecord()))
            ->filters([StudentFilters::gender()])
            ->toolbarActions([
                TakeFeeCollectionRelationActions::bulkMarkPaidAction($this->getOwnerRecord()),
                TakeFeeCollectionRelationActions::bulkMarkUnpaidAction($this->getOwnerRecord()),
            ]);
    }
}
