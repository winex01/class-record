<?php

namespace  App\Services;

use App\Enums\Gender;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

final class Field
{
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
