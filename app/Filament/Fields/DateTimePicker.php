<?php

namespace App\Filament\Fields;

use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Forms\Components\TimePicker as BaseTimePicker;

class TimePicker extends BaseTimePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($component): string => Str::headline($component->getName()))
            ->seconds(false)
            ->extraInputAttributes([
                'onclick' => 'this.showPicker && this.showPicker()',
            ])
            ->suffixAction(
                Action::make('clear')
                    ->icon('heroicon-o-x-mark')
                    ->tooltip('Clear')
                    ->action(function ($component) {
                        $component->state(null);
                    })
                    ->visible(fn ($component) => filled($component->getState()))
            );
    }
}
