<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Forms\Components\Select as BaseSelect;

class Select extends BaseSelect
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => $this->formatLabel($component->getName()))
            ->preload()
            ->searchable();
    }

    protected function formatLabel(string $name): string
    {
        $name = str_replace('_id', '', $name);
        return Str::headline($name);
    }
}
