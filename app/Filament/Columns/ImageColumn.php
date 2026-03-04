<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\ImageColumn as BaseImageColumn;

class ImageColumn extends BaseImageColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->width('1%')
            ->toggleable(isToggledHiddenByDefault: false)
            ->circular();
    }
}
