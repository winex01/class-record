<?php

namespace App\Filament\Resources\Notes;

use App\Models\Note;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\Notes\Pages\ManageNotes;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $recordTitleAttribute = 'note';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group2;
    protected static ?int $navigationSort = 270;

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::events();
    }

    public static function getNavigationBadge(): ?string
    {
        return '◉';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // Filament will use emerald background
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...static::getForm(),
            ]);
    }

    public static function getForm()
    {
        return [
            Textarea::make('note')
                ->rows(5)
                ->required(),

            Field::tags('tags'),

            Field::timestmap('starts_at')
                ->default(now()->startOfDay())
                ->required(),

            Field::timestmap('ends_at')
                ->default(now()->endOfDay())
                ->required(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->columns([
                Column::text('note'),
                Column::tags('tags'),
                Column::timestamp('starts_at'),
                Column::timestamp('ends_at'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->modalWidth(Width::Medium),
                    EditAction::make()->modalWidth(Width::Medium),
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
            'index' => ManageNotes::route('/'),
        ];
    }
}
