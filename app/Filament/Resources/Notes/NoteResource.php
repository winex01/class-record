<?php

namespace App\Filament\Resources\Notes;

use App\Models\Note;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use App\Filament\Fields\Textarea;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TagsInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Fields\DateTimePicker;
use App\Filament\Columns\DateTimeColumn;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Notes\Pages\ManageNotes;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $recordTitleAttribute = 'note';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group2;

    protected static ?int $navigationSort = 270;

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedClipboard;
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

            TagsInput::make('tags'),

            DateTimePicker::make('starts_at')
                ->default(now()->startOfDay())
                ->beforeOrEqual('ends_at')
                ->required(),

            DateTimePicker::make('ends_at')
                ->default(now()->endOfDay())
                ->afterOrEqual('starts_at')
                ->required(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->columns([
                TextColumn::make('note'),
                TagsColumn::make('tags'),
                DateTimeColumn::make('starts_at')->dateTime(),
                DateTimeColumn::make('ends_at')->dateTime(),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Medium),
                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Note')
                    ->modalWidth(Width::Medium),

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
