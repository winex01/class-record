<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput;

class NumericInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => Str::headline($component->getName()))
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
            ->step(0.01);
    }
}
