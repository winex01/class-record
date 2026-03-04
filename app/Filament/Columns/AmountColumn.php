<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class AmountColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->wrap()
            ->prefix('₱')
            ->formatStateUsing(fn ($state) => number_format($state, 2));
    }
}
