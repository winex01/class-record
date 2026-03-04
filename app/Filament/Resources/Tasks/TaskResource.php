<?php

namespace App\Filament\Resources\Tasks;

use App\Models\Task;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TagsInput;
use Filament\Actions\DeleteAction;
use App\Filament\Columns\TextColumn;
use App\Filament\Fields\BooleanToggle;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Fields\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Tasks\Pages\ManageTasks;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group2;

    protected static ?int $navigationSort = 260;

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::tasks();
    }

    public static function getNavigationBadge(): ?string
    {
        return '◉';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
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

            Repeater::make('checklists')
                ->schema([
                    Textarea::make('name')
                        ->placeholder('Subtask name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2)
                        ->rows(1),

                    BooleanToggle::make('complete')
                        ->columnSpan(1)
                        ->icons(null)
                ])
                ->defaultItems(0)
                ->columns(3)
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
                Column::timestamp('starts_at')->dateTime(),
                Column::timestamp('ends_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTasks::route('/'),
        ];
    }
}
