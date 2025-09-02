<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum NavigationGroup implements HasLabel
{
    case ClassManagement;

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::ClassManagement => 'Class Management',
        };
    }
}
