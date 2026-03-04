<?php

namespace App\Filament\Columns;

use Filament\Tables\Columns\ImageColumn as BaseImageColumn;

class ImageColumn extends BaseImageColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->toggleable(isToggledHiddenByDefault: false)
            ->circular()
            ->width('1%');
    }
}
