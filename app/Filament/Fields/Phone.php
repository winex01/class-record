<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\TextInput;

class Phone extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->maxLength(255)
            ->maxLength(30)
            ->rule('regex:/^[0-9+\-\s()]+$/')
            ->validationMessages([
                'regex' => 'The contact number may only contain numbers, plus (+), dashes (-), spaces, and parentheses ().',
            ]);
    }
}
