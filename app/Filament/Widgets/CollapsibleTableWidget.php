<?php

namespace App\Filament\Widgets;

use Livewire\Attributes\On;

abstract class CollapsibleTableWidget extends \Filament\Widgets\TableWidget
{
    protected string $view = 'filament.widgets.collapsible-table-widget';

    #[On('refreshCollapsibleTableWidget')]
    public function refreshWidget(): void
    {
        $this->resetTable();
    }

    public function getCollapsibleBadge(): int|string|null
    {
        return null; // override in child class to show a badge
    }

    public function getCollapsibleBadgeColor(): string
    {
        return 'success';
    }
}
