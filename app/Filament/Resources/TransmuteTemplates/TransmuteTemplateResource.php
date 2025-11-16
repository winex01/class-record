<?php

namespace App\Filament\Resources\TransmuteTemplates;

use App\Services\Icon;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Models\TransmuteTemplate;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\TransmuteTemplates\Pages\ManageTransmuteTemplates;
use App\Filament\Resources\TransmuteTemplates\RelationManagers\TransmuteTemplateRangesRelationManager;

class TransmuteTemplateResource extends Resource
{
    protected static ?string $model = TransmuteTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group1;

    protected static ?int $navigationSort = 400;

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return Icon::transmutations();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    // unique combine tenant/user_id and name column
                    ->unique(
                        table: 'transmute_templates',
                        modifyRuleUsing: function ($rule) {
                            return $rule->where('user_id', auth()->id());
                        }
                    )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name'),
                TextColumn::make('transmute_template_ranges_count')
                    ->label('Ranges Count')
                    ->badge()
                    ->counts('transmuteTemplateRanges'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                static::createRangesAction(),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('createRanges');
    }

    public static function createRangesAction()
    {
        return RelationManagerAction::make('createRanges')
            ->label('Table Ranges')
            ->color('info')
            ->icon('heroicon-o-plus')
            ->slideOver()
            ->modalHeading(fn ($record) => $record->name)
            ->relationManager(TransmuteTemplateRangesRelationManager::make());
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransmuteTemplates::route('/'),
        ];
    }
}
