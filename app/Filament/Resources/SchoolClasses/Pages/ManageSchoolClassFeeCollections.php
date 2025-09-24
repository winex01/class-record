<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use BackedEnum;
use App\Services\Field;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassFeeCollections extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'feeCollections';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('amount')
                        ->required()
                        ->numeric(),

                    Field::date('date'),

                ])->columnSpan(1),

                Section::make()->schema([

                    Textarea::make('description')
                        ->rows(2)
                        ->placeholder('Additional details...')
                        ->autosize(),

                    ToggleButtons::make('is_collected')
                        ->label('Collected')
                        ->inline()
                        ->default(false)
                        ->boolean(),

                ])->columnSpan(1),


            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // TODO::
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
