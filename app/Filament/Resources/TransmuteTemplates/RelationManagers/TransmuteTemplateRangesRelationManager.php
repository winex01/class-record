<?php

namespace App\Filament\Resources\TransmuteTemplates\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\TransmuteTemplates\Forms\TransmuteTemplateRangesForm;

class TransmuteTemplateRangesRelationManager extends RelationManager
{
    protected static string $relationship = 'transmuteTemplateRanges';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(TransmuteTemplateRangesForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('initial_min', 'desc')
            ->columns([
                TextColumn::make('initial_min'),
                TextColumn::make('initial_max'),
                TextColumn::make('transmuted_grade')
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New Range')
                    ->modalWidth(Width::Large)
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
