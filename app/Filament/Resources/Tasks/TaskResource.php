<?php

namespace App\Filament\Resources\Tasks;

use App\Models\Task;
use App\Services\Field;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->placeholder('Optional...'),

                Field::tags('tags'),

                Field::timestmap('starts_at')
                    ->default(now()->startOfDay())
                    ->required(),

                Field::timestmap('ends_at')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTasks::route('/'),
        ];
    }
}
