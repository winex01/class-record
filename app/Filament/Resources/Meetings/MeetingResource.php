<?php

namespace App\Filament\Resources\Meetings;

use App\Models\Meeting;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use App\Filament\Fields\Textarea;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Fields\DateTimePicker;
use App\Filament\Resources\Meetings\Pages\ManageMeetings;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group2;

    protected static ?int $navigationSort = 280;

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
        return 'purple';
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

            Textarea::make('description')
                ->placeholder('Optional...'),

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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('description')
                    ->toggleable(isToggledHiddenByDefault: true),

                Column::tags('tags'),
                Column::timestamp('starts_at'),
                Column::timestamp('ends_at'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Medium),
                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMeetings::route('/'),
        ];
    }
}
