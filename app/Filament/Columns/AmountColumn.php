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
            ->toggleable(isToggledHiddenByDefault:false)
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->wrap()
            ->prefix('₱')
            ->formatStateUsing(fn ($state) => is_numeric($state) ? number_format((float) $state, 2) : $state);
    }
}
