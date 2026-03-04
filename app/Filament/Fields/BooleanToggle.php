<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Forms\Components\ToggleButtons;

class BooleanToggle extends ToggleButtons
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => Str::headline($component->getName()))
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
