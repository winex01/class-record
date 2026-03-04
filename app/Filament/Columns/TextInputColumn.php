<?php

namespace App\Filament\Columns;

use Filament\Tables\Columns\TextInputColumn as BaseTextInputColumn;

class TextInputColumn extends BaseTextInputColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->sortable()
            ->searchable()
            ->width('1%')
            ->rules(['numeric', 'min:0', 'max:99999999']);
    }
}
