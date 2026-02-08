<div x-data="{ activeTab: $wire.entangle('activeTab').live }">
    <div class="flex justify-center mb-4">
        <x-filament::tabs>
            <x-filament::tabs.item
                alpine-active="activeTab === 'all'"
                x-on:click="activeTab = 'all'"
            >
                All
                <x-filament::badge>
                    {{ count($studentsData) }}
                </x-filament::badge>
            </x-filament::tabs.item>

            <x-filament::tabs.item
                alpine-active="activeTab === 'high_performers'"
                x-on:click="activeTab = 'high_performers'"
            >
                High Performers
                <x-filament::badge color="success">
                    {{ count($highPerformersData) }}
                </x-filament::badge>
            </x-filament::tabs.item>

            <x-filament::tabs.item
                alpine-active="activeTab === 'low_performers'"
                x-on:click="activeTab = 'low_performers'"
            >
                Low Performers
                <x-filament::badge color="danger">
                    {{ count($lowPerformersData) }}
                </x-filament::badge>
            </x-filament::tabs.item>
        </x-filament::tabs>
    </div>

    <div>
        {{ $this->table }}
    </div>
</div>
