<?php
/*
TODO:: when saving file_path make sure dont incldue the full path of the local, pehaps instead of using storage_path, use Storage::disk
TODO:: dont hard code public.pem like put it in property but instead use the previous approach using files so its easy to change/replace
TOOD:: add rate limit on activate license
TODO:: remove SSL on locahost by HERD and check if APP_ID copyable still works if it no longer works find a work around or manually create suffix action to copy
TODO:: remove artisan generate license and create different app for my generate license and monitor income
TODO:: wrap this into .exe bundle

TODO:: Grades: review computations on components and etc
TODO:: check all table search if there is an error, including the modals and tables.
TODO:: check all tables column sortable/order

Code Convention
Forms
    -StudentForm.php
        ->schema()
        ->selectOptions()
Columns
    -StudentColumns.php
        ->schema()
Actions
    -StudentActions.php
        ->createAction()
        ->createRangesAction()
Filters
    -StudentFilters.php
        ->genderFilter()


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
