<?php

namespace App\Filament\Fields;

use Filament\Actions\Action;
use Filament\Forms\Components\TimePicker as BaseTimePicker;

class TimePicker extends BaseTimePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
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
            );
    }
}
