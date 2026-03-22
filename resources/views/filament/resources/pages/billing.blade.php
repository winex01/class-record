{{-- TODO:: into package --}}
<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-4">
            {{ ($this->activateAction)(['app_id' => $this->app_id]) }}
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-panels::page>
