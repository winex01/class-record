<?php

namespace  App\Services;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;

final class Field
{
    public static function tags($name = 'tags')
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
