<?php

namespace App\Filament\Columns;

use Filament\Tables\Columns\SelectColumn as BaseSelectColumn;

class SelectColumn extends BaseSelectColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->placeholder('-')
            ->disablePlaceholderSelection()
            ->sortable()
            ->searchable()
            ->native(false)
            ->width('1%');
    }
}
