<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Application Backup
        </x-slot>

        <x-slot name="description">
            Manually trigger a full backup of your application.
        </x-slot>

        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Last backup: {{ $this->getLastBackupTime() }}
            </div>

            {{ ($this->backupAction)(['class' => 'w-full']) }}
        </div>

        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>
