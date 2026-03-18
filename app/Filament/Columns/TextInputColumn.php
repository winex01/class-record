<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextInputColumn as BaseTextInputColumn;

class TextInputColumn extends BaseTextInputColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->toggleable(isToggledHiddenByDefault: false)
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->width('1%')
            ->sortable()
            ->searchable()
            ->rules(['numeric', 'min:0', 'max:99999999']);
    }
}
