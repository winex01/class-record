<div x-data="{ activeTab: 'all' }" class="contents">
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

    <div x-show="activeTab === 'all'">
        @livewire('attendance-overview-table', ['schoolClassId' => $schoolClassId, 'studentsData' => $studentsData], key('all-tab'))
    </div>

    <div x-show="activeTab === 'perfect'">
        @livewire('attendance-overview-table', ['schoolClassId' => $schoolClassId, 'studentsData' => $perfectAttendanceData], key('perfect-tab'))
    </div>
</div>
