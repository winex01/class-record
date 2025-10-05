<?php

namespace  App\Services;

use App\Enums\Gender;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\DateTimePicker;

final class Field
{
    public static function toggleBoolean($name)
    {
        return ToggleButtons::make($name)
                    ->boolean()
                    ->default(false)
                    ->inline()
                    ->grouped()
                    ->options(SelectOption::yesOrNo())
                    ->icons([
                        true => 'heroicon-o-check',
                        false => 'heroicon-o-x-mark',
                    ])
                    ->colors([
                        true => 'success',
                        false => 'danger',
                    ]);
    }

    public static function timePicker($name)
    {
        return TimePicker::make($name)
            ->extraInputAttributes([
                'onclick' => 'this.showPicker && this.showPicker()',
            ]);
    }

    public static function dateTimePicker($name)
    {
        return DateTimePicker::make($name)
            ->extraInputAttributes([
                'onclick' => 'this.showPicker && this.showPicker()',
            ])
            ->seconds(false);
    }

    public static function gender($name = 'gender')
    {
        return Select::make($name)
            ->enum(Gender::class)
            ->options(Gender::class)
            ->required();
    }

    public static function date($name)
    {
        return DatePicker::make($name)
            ->native(false);
    }

    public static function tags($name)
    {
        $lowerName = strtolower($name);
        return TagsInput::make($name)
            ->hint('Use Tab key or Enter key to add multiple '.$lowerName)
            ->separator(',')
            ->splitKeys(['Tab']);
    }

    public static function phone($name)
    {
        return TextInput::make($name)
            ->maxLength(255)
            ->maxLength(30)
            ->rule('regex:/^[0-9+\-\s()]+$/')
            ->validationMessages([
                'regex' => 'The contact number may only contain numbers, plus (+), dashes (-), spaces, and parentheses ().',
            ]);
    }
}
