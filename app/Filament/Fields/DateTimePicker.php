<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\DateTimePicker as BaseDateTimePicker;

class DateTimePicker extends BaseDateTimePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->seconds(false)
            ->extraInputAttributes([
                'onclick' => 'this.showPicker && this.showPicker()',
            ]);
    }
}
