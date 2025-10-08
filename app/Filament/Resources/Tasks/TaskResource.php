<?php

namespace App\Filament\Resources\Tasks;

use App\Models\Task;
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

            Field::tags('tags'),

            Field::dateTimePicker('starts_at')
                ->default(now()->startOfDay())
                ->required(),

            Field::dateTimePicker('ends_at')
                ->default(now()->endOfDay())
                ->required(),

            Repeater::make('checklists')
                ->schema([
                    TextInput::make('name')
                        ->placeholder('Subtask name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Field::toggleBoolean('complete')
                        ->columnSpan(1)

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
                Column::text('name'),
                Column::text('description')
                    ->toggleable(isToggledHiddenByDefault: true),

                Column::tags('tags'),
                Column::timestamp('starts_at')->dateTime(),
                Column::timestamp('ends_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->modalWidth(Width::Large),
                    EditAction::make()->modalWidth(Width::Large),
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
            'index' => ManageTasks::route('/'),
        ];
    }
}
