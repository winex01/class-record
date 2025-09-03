<?php

namespace App\Services;

final class Action
{
    public static function back($resource = null)
    {
        $action = \Filament\Actions\Action::make('back')
            ->icon('heroicon-m-arrow-left')
            ->color('gray');

        if ($resource) {
            $action->url($resource::getUrl('index'));
        }

        return $action;
    }

}
