<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FeeCollectionStatus: string implements HasLabel, HasColor, HasIcon
{
    case PAID = 'PAID';
    case UNPAID = 'UNPAID';
    case PARTIAL = 'PARTIAL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAID => 'Completed',
            self::UNPAID => 'Unpaid',
            self::PARTIAL => 'Partial',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PAID => 'success',
            self::UNPAID => 'warning',
            self::PARTIAL => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PAID => 'heroicon-m-check',
            self::UNPAID => 'heroicon-m-x-circle',
            self::PARTIAL => 'heroicon-m-exclamation-circle',
        };
    }
}
