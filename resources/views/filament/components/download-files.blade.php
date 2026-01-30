{{-- resources/views/filament/components/download-files.blade.php --}}

<div class="space-y-3">
    @forelse($files as $file)
        <div class="flex items-center space-x-2">
            {{-- Bullet Point --}}
            <div class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0"></div>

            {{-- Download Link --}}
            <a href="{{ Storage::url($file) }}"
               download="{{ basename($file) }}"
               class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 hover:underline font-medium">
                {{ basename($file) }}
            </a>
        </div>
    @empty
        <div class="text-center py-4 text-gray-500">
            <p>No files attached</p>
        </div>
    @endforelse
</div>
