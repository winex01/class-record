{{--
    TODO:: cant download file and also group the array files using the name.
--}}
<div class="space-y-2">
    @forelse($files as $file)
        @php
            $fileName = basename($file);
            $extension = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
        @endphp

        <a href="{{ Storage::url($file) }}"
           download="{{ $fileName }}"
           class="group flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 hover:border-primary-400 dark:hover:border-primary-600 transition-all duration-200 hover:shadow-md">

            {{-- Left side: Icon + File name --}}
            <div class="flex items-center space-x-3 flex-1 min-w-0">
                {{-- File Icon --}}
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 group-hover:from-primary-100 group-hover:to-primary-200 dark:group-hover:from-primary-800/40 dark:group-hover:to-primary-700/40 transition-all">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>

                {{-- File Info --}}
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors"></p>
                        {{ $fileName }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $extension }} File
                    </p>
                </div>
            </div>

            {{-- Right side: Download icon --}}
            <div class="flex-shrink-0 ml-3">
                <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-all">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300 group-hover:text-primary-700 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
            </div>
        </a>
    @empty
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 font-medium">No files attached</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Files will appear here when attached</p>
        </div>
    @endforelse
</div>

@if(count($files) > 0)
    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
            💡 <strong>Tip:</strong> Click on any file to download it
        </p>
    </div>
@endif
