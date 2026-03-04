<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;

class TextColumn extends BaseTextColumn
{
    protected bool $isUnderlined = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->toggleable(isToggledHiddenByDefault: false)
            ->wrap()
            ->sortable()
            ->searchable();
    }

    public function underline(bool $condition = true): static
    {
        $this->isUnderlined = $condition;

        return $this;
    }

    public function getExtraAttributes(): array
    {
        return $this->isUnderlined
            ? array_merge(parent::getExtraAttributes(), ['class' => 'cursor-pointer hover:underline'])
            : parent::getExtraAttributes();
    }
}
