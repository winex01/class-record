<?php

namespace App\Services;

use Carbon\Carbon;

final class Helper
{
    public static function getDayName(Carbon $date)
    {
        return strtolower($date->dayName);
    }

    public static function weekDays(): array
    {
        return [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
    }
}
