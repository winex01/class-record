<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class BooleanColumn extends TextColumn
{
    protected string $trueLabel = 'Yes';
    protected string $falseLabel = 'No';
    protected string $trueIcon = 'heroicon-o-check';
    protected string $falseIcon = 'heroicon-o-x-mark';
    protected string $trueColor = 'success';
    protected string $falseColor = 'danger';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->toggleable(isToggledHiddenByDefault: false)
            ->width('1%')
            ->badge()
            ->sortable()
            ->formatStateUsing(fn($state) => $state ? $this->trueLabel : $this->falseLabel)
            ->icon(fn($state) => $state ? $this->trueIcon : $this->falseIcon)
            ->color(fn($state) => $state ? $this->trueColor : $this->falseColor);
    }

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;
        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;
        return $this;
    }

    public function trueIcon(string $icon): static
    {
        $this->trueIcon = $icon;
        return $this;
    }

    public function falseIcon(string $icon): static
    {
        $this->falseIcon = $icon;
        return $this;
    }

    public function trueColor(string $color): static
    {
        $this->trueColor = $color;
        return $this;
    }

    public function falseColor(string $color): static
    {
        $this->falseColor = $color;
        return $this;
    }
}
