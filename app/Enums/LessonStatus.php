<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LessonStatus: string implements HasLabel, HasColor
{
    case TOPICS = 'TOPICS';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TOPICS => 'Topics',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::TOPICS => 'gray',
            self::IN_PROGRESS => 'warning',
            self::DONE => 'success',
        };
    }
}
