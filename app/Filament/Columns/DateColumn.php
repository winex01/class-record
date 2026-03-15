<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class DateColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->toggleable(isToggledHiddenByDefault: false)
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->wrap()
            ->date()
            ->sortable()
            ->searchable(query: function ($query, string $search, $column) {
                $name = $column->getName();
                return $query->whereRaw(
                    "DATE_FORMAT({$name}, '%b %d, %Y') LIKE ?",
                    ["%{$search}%"]
                );
            });
    }
}
