<?php

namespace App\Filament\Resources\Recurrings\Forms;

use App\Services\Helper;
use App\Filament\Fields\Textarea;
use App\Filament\Fields\TagsInput;
use App\Filament\Fields\TextInput;
use App\Filament\Fields\DatePicker;
use App\Filament\Fields\TimePicker;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Tabs\Tab;

class RecurringForm
{
    public static function schema()
    {
        return [
            Tabs::make('Tabs')
            ->tabs([
                Tab::make('Details')
                    ->schema(static::detailsField()),

                Tab::make('Weekdays')
                    ->schema([
                        Radio::make('Weekdays')
                            ->hiddenLabel(true)
                            ->default(true)
                            ->options([true => 'Weekdays'])
                            ->afterStateHydrated(fn ($set) => $set('Weekdays', true))
                            ->markAsRequired()
                            ->dehydrated(false)
                            ->rules([
                                fn ($get) => function (string $attribute, $value, $fail) use ($get) {
                                    $error = true;

                                    foreach (Helper::weekDays() as $day) {
                                        $dayValue = $get($day) ?? [];

                                        if (!empty($dayValue)) {
                                            foreach ($dayValue as $item) {
                                                if (
                                                    !empty($item['starts_at'] ?? null) ||
                                                    !empty($item['ends_at'] ?? null)
                                                ) {
                                                    $error = false;
                                                    break 2; // break both loops
                                                }
                                            }
                                        }
                                    }

                                    if ($error) {
                                        $fail('At least one day of the week must have both start and end times.');
                                    }
                                },
                            ]),

                        ...collect(Helper::weekDays())
                            ->flatMap(fn ($day) => static::dayField($day))
                            ->toArray()
                    ]),
            ])
        ];
    }

    public static function detailsField()
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->placeholder('Optional...'),

            TagsInput::make('tags'),

            DatePicker::make('date_start')
                ->helperText('The recurring event becomes active starting on this date.')
                ->beforeOrEqual('date_end')
                ->default(now()),

            DatePicker::make('date_end')
                ->helperText('The recurring event will stop or end on this date.')
                ->afterOrEqual('date_start')
                ->default(now()),
        ];
    }

    public static function dayField($day)
    {
        return [
            Repeater::make($day)
                ->schema([
                    Grid::make()
                        ->schema([
                            TimePicker::make('starts_at')
                                ->requiredWith('ends_at')
                                ->columnSpan(1),

                            TimePicker::make('ends_at')
                                ->requiredWith('starts_at')
                                ->columnSpan(1),
                        ])
                ])
                ->columns(3)
                ->orderColumn(false)
                ->addable(false)
                ->deletable(false)
            ];
    }
}
