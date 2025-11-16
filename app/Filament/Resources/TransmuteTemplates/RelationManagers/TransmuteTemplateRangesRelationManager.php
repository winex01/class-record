<?php

namespace App\Filament\Resources\TransmuteTemplates\RelationManagers;

use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassGrades;

class TransmuteTemplateRangesRelationManager extends RelationManager
{
    protected static string $relationship = 'transmuteTemplateRanges';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...ManageSchoolClassGrades::rangesField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Column::text('initial_min'),
                Column::text('initial_max'),
                Column::text('transmuted_grade'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New ' . strtolower($this->getOwnerRecord()->name) . ' range')
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
