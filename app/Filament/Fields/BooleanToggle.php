<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\ToggleButtons;

class BooleanToggle extends ToggleButtons
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->boolean()
            ->default(false)
            ->inline()
            ->grouped()
            ->options([
                true => 'Yes',
                false => 'No',
            ])
            ->icons([
                true => 'heroicon-o-check',
                false => 'heroicon-o-x-mark',
            ])
            ->colors([
                true => 'success',
                false => 'danger',
            ]);
    }
}
