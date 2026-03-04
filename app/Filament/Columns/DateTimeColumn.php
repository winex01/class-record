<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class DateTimeColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->wrap()
            ->dateTime()
            ->tooltip(fn ($record, $column) => 'Search: ' . $record->{$column->getName()});
    }
}
