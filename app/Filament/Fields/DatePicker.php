<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\DatePicker as BaseDatePicker;

class DatePicker extends BaseDatePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->native(false);
    }
}
