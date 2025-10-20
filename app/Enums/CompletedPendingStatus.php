<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CompletedPendingStatus: string implements HasLabel
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
}
