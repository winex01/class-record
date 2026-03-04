<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\SelectColumn as BaseSelectColumn;

class SelectColumn extends BaseSelectColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->placeholder('-')
            ->disablePlaceholderSelection()
            ->sortable()
            ->searchable()
            ->native(false)
            ->width('1%');
    }
}
