<?php

namespace App\Filament\Resources\AssessmentTypes;

use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\AssessmentType;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\AssessmentTypes\Pages\ManageAssessmentTypes;

class AssessmentTypeResource extends Resource
{
    protected static ?string $model = AssessmentType::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group1;

    protected static ?int $navigationSort = 300;

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::assessmentTypes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...static::getForm()
            ]);
    }

    public static function getForm()
    {
        return [
            TextInput::make('name')
                    ->required()
                    ->maxLength(255),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth(Width::Medium),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAssessmentTypes::route('/'),
        ];
    }
}
