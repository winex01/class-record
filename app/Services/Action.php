<?php

namespace App\Services;

final class Action
{
    public static function back($resource)
    {
        return \Filament\Actions\Action::make('back')
            ->icon('heroicon-m-arrow-left')
            ->url($resource::getUrl('index'))
            ->color('gray');
    }
}
