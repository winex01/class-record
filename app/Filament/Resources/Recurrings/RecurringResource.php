<?php

namespace App\Filament\Resources\Recurrings;

use App\Services\Field;
use App\Services\Column;
use App\Services\Helper;
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
use Filament\Schemas\Components\Tabs;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
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
                ...static::getForm(false)
            ]);
    }

    public static function getForm($weekdayRepeaterSetting = true)
    {
        return [
            Tabs::make('Tabs')
                ->tabs([
                    Tab::make('Details')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            Textarea::make('description')
                                ->placeholder('Optional...'),

                            Field::tags('tags'),
                        ]),

                    Tab::make('Weekdays')
                        ->schema([
                            Field::date('effectivity_date')
                                ->helperText('Takes effect starting on this date.')
                                ->default(now()),

                            ...collect(Helper::weekDays())
                                ->flatMap(fn ($day) => static::dayField($day, $weekdayRepeaterSetting))
                                ->toArray()
                        ]),
                ])
        ];
    }

    public static function dayField($day, $addableDeletable = true)
    {
        return [
            Repeater::make($day)
                ->schema([
                    Grid::make()
                        ->schema([
                            Field::timePicker('starts_at')
                                ->columnSpan(1),

                                Field::timePicker('ends_at')
                                ->columnSpan(1),
                        ])
                ])
                ->columns(3)
                ->orderable(false)
                ->minItems(1)
                ->maxItems(1)
                ->defaultItems(1)
                ->addable($addableDeletable)
                ->deletable($addableDeletable)
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
                Column::date('effectivity_date'),
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
