@if($record)
    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: space-between;">

        <!-- Name -->
        <div class="flex items-center gap-2">
            <div class="flex-shrink-0">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="h-5 w-5 text-primary-500"
                />
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Name</p>
                <p class="text-xs font-semibold text-gray-900 dark:text-white">
                    {{ $record->name ?? 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Date -->
        <div class="flex items-center gap-2">
            <div class="flex-shrink-0">
                <x-filament::icon
                    icon="heroicon-o-calendar-days"
                    class="h-5 w-5 text-info-500"
                />
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Date</p>
                <p class="text-xs font-semibold text-gray-900 dark:text-white">
                    {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('M d, Y') : 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Amount -->
        <div class="flex items-center gap-2">
            <div class="flex-shrink-0">
                <x-filament::icon
                    icon="heroicon-o-banknotes"
                    class="h-5 w-5 text-success-500"
                />
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Amount</p>
                <p class="text-xs font-semibold text-gray-900 dark:text-white">
                    {{ $record->amount ? number_format($record->amount, 2) : '—' }}
                </p>
            </div>
        </div>

    </div>
@endif
