<?php

namespace App\Filament\Resources\MyFiles\Actions;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;

class MyFileActions
{
    public static function viewAction()
    {
        return ViewAction::make()
            ->modalWidth(Width::Medium);
    }
}
