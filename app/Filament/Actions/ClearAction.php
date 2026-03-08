<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class ClearAction extends Action
{
    public static function make(?string $name = 'clearAction'): static
    {
        return parent::make($name)
            ->tooltip('Clear')
            ->icon('heroicon-m-x-mark')
            ->color('gray')
            ->action(fn ($component) => $component->state(null))
            ->hidden(fn ($component) => blank($component->getState()));
    }
}
