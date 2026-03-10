<x-filament-widgets::widget class="fi-wi-table">
    <x-filament::section :collapsible="true" :collapsed="true" :heading="static::$heading">
        <div id="birthday-widget-table">
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}
            {{ $this->table }}
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
        </div>
    </x-filament::section>

    <style>
        #birthday-widget-table .fi-ta {
            border: none !important;
            box-shadow: none !important;
        }
        #birthday-widget-table .fi-ta-ctn {
            border: none !important;
            box-shadow: none !important;
            ring: none !important;
        }
    </style>
</x-filament-widgets::widget>
