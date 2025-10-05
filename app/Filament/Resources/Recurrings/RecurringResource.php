<?php

namespace App\Filament\Resources\Recurrings;

use App\Services\Field;
use App\Models\Recurring;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\Recurrings\Pages\ManageRecurrings;

class RecurringResource extends Resource
{
    protected static ?string $model = Recurring::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static string | \UnitEnum | null $navigationGroup = \App\Enums\NavigationGroup::Group2;

    protected static ?int $navigationSort = 280;

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::recurrings();
    }

    public static function getNavigationBadge(): ?string
    {
        return '◉';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'pink';
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

                Field::date('effectivity_date')
                    ->helperText('Takes effect starting on this date.')
                    ->default(now()),

                Section::make('Weekdays')
                    ->schema([
                        ...static::dayField('monday'),
                        ...static::dayField('tuesday'),
                        ...static::dayField('wednesday'),
                        ...static::dayField('thursday'),
                        ...static::dayField('friday'),
                        ...static::dayField('saturday'),
                        ...static::dayField('sunday'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function dayField($day)
    {
        return [
            TextInput::make($day)
                ->label('Day')
                ->hiddenLabel(($day === 'monday' ? false : true))
                ->default(ucfirst($day))
                ->readOnly()
                ->columnSpan(1),

            Grid::make()
                ->schema([
                    Field::timePicker($day.'_'.'starts_at')
                        ->label('Starts at')
                        ->hiddenLabel(($day === 'monday' ? false : true))
                        ->default(now()->startOfDay())
                        ->columnSpan(1),

                    Field::timePicker($day.'_'.'ends_at')
                        ->label('Ends at')
                        ->hiddenLabel(($day === 'monday' ? false : true))
                        ->default(now()->endOfDay())
                        ->columnSpan(1),
                ])
                ->columnSpan(2),
            ];
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
                ActionGroup::make([
                    ViewAction::make()->modalWidth(Width::ExtraLarge),
                    EditAction::make()->modalWidth(Width::ExtraLarge),
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
            'index' => ManageRecurrings::route('/'),
        ];
    }
}
