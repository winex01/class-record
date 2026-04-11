<?php

namespace App\Filament\Resources\AssessmentTypes;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationGroup;
use App\Models\AssessmentType;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\AssessmentTypes\Schemas\AssessmentTypeForm;
use App\Filament\Resources\AssessmentTypes\Pages\ManageAssessmentTypes;

class AssessmentTypeResource extends Resource
{
    protected static ?string $model = AssessmentType::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static string | \UnitEnum | null $navigationGroup = NavigationGroup::Group1;
    protected static ?int $navigationSort = 400;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedSquare2Stack;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(AssessmentTypeForm::getFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Type')
                    ->modalWidth(Width::Medium),

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
