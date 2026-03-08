<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;

class DeleteAllAction extends Action
{
    public static function make(?string $name = 'deleteAll'): static
    {
        return parent::make($name)
            ->label('Delete All')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalFooterActionsAlignment(Alignment::Center)
            ->action(function ($component) {
                // Clear all items
                $component->state([]);
            });
    }
}
