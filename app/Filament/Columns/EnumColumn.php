<?php

namespace App\Filament\Columns;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;

class EnumColumn extends TextColumn
{
    protected string $enumClass;

    public function enum(string $enumClass): static
    {
        $this->enumClass = $enumClass;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->toggleable(isToggledHiddenByDefault: false)
            ->wrap()
            ->sortable()
            ->searchable()
            ->label(fn ($column): string => Str::headline($column->getName()))
            ->icon(fn ($state) => $this->getEnumValue($state, 'getIcon'))
            ->color(fn ($state) => $this->getEnumValue($state, 'getColor'))
            ->formatStateUsing(function ($state) {
                $enumInstance = $this->enumClass::tryFrom($state);

                if ($enumInstance && method_exists($enumInstance, 'getLabel')) {
                    return $enumInstance->getLabel();
                }

                return $enumInstance ? $enumInstance->name : $state;
            });
    }

    protected function getEnumValue($state, string $method)
    {
        $enumInstance = $this->enumClass::tryFrom($state);
        return $enumInstance && method_exists($enumInstance, $method)
            ? $enumInstance->$method()
            : null;
    }
}
