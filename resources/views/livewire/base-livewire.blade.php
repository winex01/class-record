<div>
    {{-- Tabs container with Filament card styling --}}
    @if(method_exists($this, 'getTabs'))
        <div class="flex justify-center mb-4">
            <x-filament::tabs >
                @foreach($this->getTabs() as $tabKey => $tab)
                    <x-filament::tabs.item
                        :active="$activeTab === $tabKey"
                        :badge="$tab->getBadge()"
                        :badge-color="$tab->getBadgeColor()"
                        :icon="$tab->getIcon()"
                        wire:key="tab-{{ $tabKey }}"
                        wire:click="$set('activeTab', '{{ $tabKey }}')"
                    >
                        {{ $tab->getLabel() ?: $tabKey }}
                    </x-filament::tabs.item>
                @endforeach
            </x-filament::tabs>
        </div>
    @endif

    {{-- Table --}}
    <div>
        {{ $this->table }}
    </div>
</div>
