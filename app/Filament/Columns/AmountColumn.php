<?php

namespace App\Filament\Columns;

use Filament\Tables\Columns\TextColumn;

class AmountColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->wrap()
            ->prefix('₱')
            ->width('1%')
            ->formatStateUsing(fn ($state) => number_format($state, 2));
    }
}
