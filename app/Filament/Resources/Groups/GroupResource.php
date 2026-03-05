<?php

namespace App\Filament\Resources\Groups;

use App\Models\Group;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Groups\Pages\ManageGroups;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group1;

    protected static ?int $navigationSort = 300;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedCube;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
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
                EditAction::make()
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Group')
                    ->modalWidth(Width::Medium),

                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGroups::route('/'),
        ];
    }

    public static function selectOptions()
    {
        return Group::all()
            ->pluck('name', 'name')
            ->prepend('-', '-')
            ->toArray();
    }
}
