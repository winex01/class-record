<?php

namespace App\Services;

use Filament\Notifications\Notification;

final class Alert
{
    public static function success($title = 'Saved')
    {
        return Notification::make()
                ->title($title)
                ->success()
                ->send();
    }

}
