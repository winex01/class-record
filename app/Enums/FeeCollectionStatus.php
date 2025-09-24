<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FeeCollectionStatus: string implements HasLabel, HasColor, HasIcon
{
    case PAID = 'PAID';
    case PARTIAL = 'PARTIAL';
    case UNPAID = 'UNPAID';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::PARTIAL => 'Partial',
            self::UNPAID => 'Unpaid',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PAID => 'success',
            self::PARTIAL => 'info',
            self::UNPAID => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PAID => 'heroicon-m-check',
            self::PARTIAL => 'heroicon-m-exclamation-circle',
            self::UNPAID => 'heroicon-m-x-circle',
        };
    }
}
