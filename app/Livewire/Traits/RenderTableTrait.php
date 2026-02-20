<?php

namespace App\Livewire\Traits;

trait RenderTableTrait
{
    public function render()
    {
        return <<<'HTML'
            <div>
                {{ $this->table }}
            </div>
        HTML;
    }
}
