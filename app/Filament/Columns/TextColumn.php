<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;

class TextColumn extends BaseTextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->toggleable(isToggledHiddenByDefault: false)
            ->wrap()
            ->sortable()
            ->searchable();
    }
}
