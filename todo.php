<?php
/*

TODO:: download app backup
TODO:: licensed Payment
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
