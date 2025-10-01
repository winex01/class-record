<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum NavigationGroup implements HasLabel
{
    case Group2;
    case Group1;

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::Group1 => 'Settings',
            self::Group2 => 'Events',
        };
    }
}
