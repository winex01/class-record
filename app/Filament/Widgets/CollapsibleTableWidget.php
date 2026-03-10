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
}
