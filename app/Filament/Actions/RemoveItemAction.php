<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;

/**
 * NOTE: Commonly used as a Repeater or Builder item removal action.
 * Skips confirmation for unsaved items (no `id`),
 * and requires confirmation for persisted records.
 *
 * Usage:
 *   Repeater::make('temp')->deleteAction(fn (Action $action) => RemoveItemAction::confirmIfSaved($action))
 */
class RemoveItemAction extends Action
{
    public static function make(?string $name = 'removeItem'): static
    {
        return parent::make($name);
    }

    public static function confirmIfSaved(Action $action): Action
    {
        return $action
            ->modalFooterActionsAlignment(Alignment::Center)
            ->requiresConfirmation(
                fn (array $arguments, $component): bool =>
                    isset($component->getRawItemState($arguments['item'])['id'])
            );
    }
}
