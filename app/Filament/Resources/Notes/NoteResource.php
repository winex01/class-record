<?php

namespace App\Filament\Resources\Notes;

use App\Models\Note;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Enums\NavigationGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Notes\Forms\NoteForm;
use App\Filament\Resources\Notes\Pages\ManageNotes;
use App\Filament\Resources\Notes\Columns\NoteColumns;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;
    protected static ?string $recordTitleAttribute = 'note';
    protected static string | \UnitEnum | null $navigationGroup = NavigationGroup::Group2;
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
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(NoteForm::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->columns(NoteColumns::schema())
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
