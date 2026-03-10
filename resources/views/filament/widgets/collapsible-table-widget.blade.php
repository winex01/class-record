<x-filament-widgets::widget class="fi-wi-table">
    <x-filament::section :collapsible="true" :collapsed="true">
        <x-slot name="heading">
            {{ static::$heading }}

            @if($badge = $this->getCollapsibleBadge())
                <x-filament::badge :color="$this->getCollapsibleBadgeColor()">
                    {{ $badge }}
                </x-filament::badge>
            @endif
        </x-slot>


        <div id="collapsible-widget-table">
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}
            {{ $this->table }}
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
        </div>
    </x-filament::section>

    <style>
        #collapsible-widget-table .fi-ta {
            border: none !important;
            box-shadow: none !important;
        }
        #collapsible-widget-table .fi-ta-ctn {
            border: none !important;
            box-shadow: none !important;
            ring: none !important;
        }
    </style>
</x-filament-widgets::widget>
