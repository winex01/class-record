<?php

namespace App\Services;

use Filament\Support\Icons\Heroicon;

final class Icon
{
    public static function classes(): Heroicon|string
    {
        return Heroicon::RectangleGroup;
    }

    public static function students(): Heroicon|string
    {
        return Heroicon::Users;
    }

    public static function attendances(): Heroicon|string
    {
        return Heroicon::CalendarDays;
    }
}
