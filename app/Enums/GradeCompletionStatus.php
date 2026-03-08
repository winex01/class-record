<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GradeCompletionStatus: string implements HasLabel, HasColor, HasIcon
{
    case COMPLETE = 'COMPLETE';
    case INCOMPLETE = 'INCOMPLETE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COMPLETE => 'Complete',
            self::INCOMPLETE => 'Incomplete',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::COMPLETE => 'success',
            self::INCOMPLETE => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::COMPLETE => 'heroicon-o-check-circle',
            self::INCOMPLETE => 'heroicon-o-x-circle',
        };
    }
}
