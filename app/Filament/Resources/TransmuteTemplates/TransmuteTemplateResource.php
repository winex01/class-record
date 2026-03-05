<?php

namespace App\Filament\Resources\TransmuteTemplates;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Models\TransmuteTemplate;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\TransmuteTemplates\Pages\ManageTransmuteTemplates;
use App\Filament\Resources\TransmuteTemplates\RelationManagers\TransmuteTemplateRangesRelationManager;

class TransmuteTemplateResource extends Resource
{
    protected static ?string $model = TransmuteTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group1;

    protected static ?int $navigationSort = 400;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedScale;
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
                TextColumn::make('name'),
            ])
            ->recordActions([
                static::createRangesAction(),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Template')
                    ->modalWidth(Width::Large)
                    ->after(function ($livewire, $record, $action) {
                        $action->close();
                        $livewire->js("
                            setTimeout(() => {
                                \$wire.mountTableAction('createRanges', {$record->getKey()})
                            }, 150)
                        ");
                    }),

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
