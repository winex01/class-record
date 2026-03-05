<?php

namespace App\Filament\Resources\TransmuteTemplates\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Schemas\Components\Grid;
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
                Grid::make(3)
                    ->schema([
                        // NOTE: Using modifyRuleUsing() to scope the unique rule to
                        // $this->getOwnerRecord()->id so validation only applies per template.
                        ...array_map(
                            fn ($field) => $field
                                ->unique(
                                    table: 'transmute_template_ranges',
                                    column: 'initial_min',
                                    modifyRuleUsing: fn ($rule) =>
                                        $rule->where(
                                            'transmute_template_id',
                                            $this->getOwnerRecord()->getKey()
                                        )
                                ),
                            ManageSchoolClassGrades::rangesField()
                        ),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('initial_min', 'desc')
            ->columns([
                TextColumn::make('initial_min'),
                TextColumn::make('initial_max'),
                TextColumn::make('transmuted_grade')
                    ->formatStateUsing(fn ($state) => $state != floor($state) ? $state : number_format($state, 0))
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
