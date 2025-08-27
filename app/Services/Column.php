<?php

namespace App\Services;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\IconPosition;

final class Column
{
    public static function text($name)
    {
        return TextColumn::make($name)
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable()
            ->searchable();
    }

    public static function enum($name, $enum, $attribute = null)
    {
        if (!$attribute) {
            $attribute = $name;
        }

        return TextColumn::make($name)
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable()
            ->searchable()
            ->label(Str::headline($attribute))
            ->iconPosition(IconPosition::After)
            ->icon(fn ($state) =>
                is_object($state) && method_exists($state, 'getIcon')
                    ? $state->getIcon()
                    : $enum::tryFrom($state)?->getIcon()
            )
            ->color(fn ($state) =>
                is_object($state) && method_exists($state, 'getColor')
                    ? $state->getColor()
                    : $enum::tryFrom($state)?->getColor()
            )
            ->getStateUsing(fn ($record) => $record->{$attribute})
            ->formatStateUsing(fn ($state) =>
                is_object($state) && method_exists($state, 'getLabel')
                    ? $state->getLabel()
                    : $enum::tryFrom($state)?->getLabel()
            );
    }
}
