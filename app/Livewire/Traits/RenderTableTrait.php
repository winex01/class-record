<?php

namespace App\Livewire\Traits;

trait RenderTableTrait
{
     /*
        NOTE:: Dont forget to add this to your mount if you use tab
        Set default active tab to the first tab key
        $this->activeTab = array_key_first($this->getTabs());
    */

    // Define the activeTab property as public
    public ?string $activeTab = null;

    // This method is called when activeTab changes
    public function updatedActiveTab()
    {
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.base-livewire');
    }
}
