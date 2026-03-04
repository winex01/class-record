<?php

namespace App\Filament\Columns;

use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;

class BooleanIconColumn extends IconColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            // ->width('1%')
            ->alignCenter()
            ->trueIcon('heroicon-o-check-circle')
            ->trueColor(Color::Emerald)
            ->falseIcon('heroicon-o-x-circle')
            ->falseColor(Color::Rose)
            ->size(IconSize::Large);
    }
}
