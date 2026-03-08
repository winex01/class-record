<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Forms\Components\Textarea as BaseTextarea;

class Textarea extends BaseTextarea
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => Str::headline($component->getName()))
            ->maxLength(65535)
            ->rows(5)
            ->nullable();
    }
}
