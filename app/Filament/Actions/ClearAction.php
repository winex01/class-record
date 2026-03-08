<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

/**
 * NOTE: Commonly used as a suffix action on form fields to clear the current value.
 * Best suited for fields where the user can select or input a single value.
 *
 * Confirmed working on:
 *   - Select::make()->suffixAction(ClearAction::make())
 */
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
