<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Forms\Components\DatePicker as BaseDatePicker;

class DatePicker extends BaseDatePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => Str::headline($component->getName()))
            ->native(false);
    }
}
