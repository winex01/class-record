<x-filament-widgets::widget>
    <x-filament::section>
        @if($record)
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Subject -->
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <x-filament::icon
                            icon="heroicon-o-academic-cap"
                            class="h-8 w-8 text-primary-500"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Subject</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $record->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Year & Section -->
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <x-filament::icon
                            icon="heroicon-o-calendar"
                            class="h-8 w-8 text-success-500"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Year & Section</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $record->year_section ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Start Date -->
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <x-filament::icon
                            icon="heroicon-o-play-circle"
                            class="h-8 w-8 text-info-500"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Start Date</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $record->date_start ? \Carbon\Carbon::parse($record->date_start)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- End Date -->
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <x-filament::icon
                            icon="heroicon-o-stop-circle"
                            class="h-8 w-8 text-warning-500"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">End Date</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $record->date_end ? \Carbon\Carbon::parse($record->date_end)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
