<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;

class TextColumn extends BaseTextColumn
{
    protected bool|\Closure $isUnderlined = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn($column): string => Str::headline($column->getName()))
            ->toggleable(isToggledHiddenByDefault: false)
            ->wrap()
            ->sortable()
            ->searchable();
    }

    public function underline(bool|\Closure $condition = true): static
    {
        $this->isUnderlined = $condition;

        return $this;
    }

    public function getExtraAttributes(): array
    {
        $isUnderlined = $this->isUnderlined instanceof \Closure
            ? $this->evaluate($this->isUnderlined)
            : $this->isUnderlined;

        return $isUnderlined
            ? array_merge(parent::getExtraAttributes(), ['class' => 'cursor-pointer hover:underline'])
            : array_merge(parent::getExtraAttributes(), []);
    }

    public function localCopyable(): static
    {
        $tooltip = 'Copied ' . Str::headline($this->getName()) . '!';

        return $this->extraAttributes([
            'x-on:click' => "
            let temp = document.createElement('textarea');
            temp.value = \$el.querySelector('span')?.innerText ?? \$el.innerText;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            \$tooltip('{$tooltip}', { timeout: 1500 });
        ",
            'class' => 'cursor-pointer',
        ])
            ->action(Action::make('temp'));
    }
}
