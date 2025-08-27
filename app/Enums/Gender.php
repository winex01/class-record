<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel, HasColor, HasIcon
{
    case MALE = 'MALE';
    case FEMALE = 'FEMALE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::MALE => 'info',
            self::FEMALE => 'pink',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::MALE => null,
            self::FEMALE => null,
        };
    }
}
