<div style="display:flex; align-items:center; gap:0.5rem;">
    <span>Take Attendance — </span>
    <x-filament::icon
        icon="heroicon-o-calendar-days"
        class="h-5 w-5 text-info-500"
    />
    <span>{{ $record->date->format('M d, Y') }}</span>
</div>
