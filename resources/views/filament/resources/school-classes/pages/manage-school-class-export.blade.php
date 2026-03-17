<x-filament-panels::page>
    <form wire:submit="export">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button
                type="submit"
                color="primary"
                wire:loading.attr="disabled"
                wire:target="export"
            >
                <!-- Normal state -->
                <span wire:loading.remove wire:target="export">
                    Generate Export
                </span>

                <!-- Loading state: spinner + text inline -->
                <span wire:loading wire:target="export">
                    Generating...
                </span>
            </x-filament::button>
        </div>

    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
