<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AssessmentStatus: string implements HasLabel, HasColor, HasIcon
{
    case COMPLETED = 'COMPLETED';
    case PENDING = 'PENDING';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::PENDING => 'Pending',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::PENDING => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::COMPLETED => 'heroicon-m-check',
            self::PENDING => 'heroicon-m-clock',
        };
    }
}
