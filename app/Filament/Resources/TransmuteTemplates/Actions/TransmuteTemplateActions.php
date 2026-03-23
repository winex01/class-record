<?php

namespace App\Filament\Resources\TransmuteTemplates\Actions;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\TransmuteTemplates\RelationManagers\TransmuteTemplateRangesRelationManager;

class TransmuteTemplateActions
{
    public static function createAction()
    {
        return
            CreateAction::make()
            ->label('New Template')
            ->modalWidth(Width::Large)
            ->after(function ($livewire, $record, $action) {
                $action->close();
                $livewire->js("
                    setTimeout(() => {
                        \$wire.mountTableAction('createRanges', {$record->getKey()})
                    }, 150)
                ");
            });
    }

    public static function createRangesAction()
    {
        return
            RelationManagerAction::make('createRanges')
            ->label('Table Ranges')
            ->color('info')
            ->icon('heroicon-o-plus')
            ->slideOver()
            ->compact()
            ->modalHeading(fn ($record) => $record->name)
            ->relationManager(TransmuteTemplateRangesRelationManager::make());
    }
}
