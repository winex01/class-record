<?php

namespace App\Filament\Resources\TransmuteTemplates;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Models\TransmuteTemplate;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\TransmuteTemplates\Schemas\TransmuteTemplateForm;
use App\Filament\Resources\TransmuteTemplates\Pages\ManageTransmuteTemplates;
use App\Filament\Resources\TransmuteTemplates\Actions\TransmuteTemplateActions;

class TransmuteTemplateResource extends Resource
{
    protected static ?string $model = TransmuteTemplate::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static string | \UnitEnum | null $navigationGroup = NavigationGroup::Group1;
    protected static ?int $navigationSort = 400;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedScale;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(TransmuteTemplateForm::getFields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
            ])
            ->recordActions([
                TransmuteTemplateActions::createRangesAction(),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                TransmuteTemplateActions::createAction(),
                DeleteBulkAction::make(),

            ])
            ->recordAction('createRanges');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransmuteTemplates::route('/'),
        ];
    }
}
