<div x-data="{ activeTab: $wire.entangle('activeTab').live }">
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
            alpine-active="activeTab === 'perfect'"
            x-on:click="activeTab = 'perfect'"
        >
            Perfect Attendance
            <x-filament::badge>
                {{ count($perfectAttendanceData) }}
            </x-filament::badge>
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="mt-4">
        {{ $this->table }}
    </div>
</div>
