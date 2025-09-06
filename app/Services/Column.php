<?php

namespace App\Services;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\ImageColumn;

final class Column
{
    public static function text($name)
    {
        return TextColumn::make($name)
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable()
            ->searchable();
    }

    public static function tags($name = 'tags')
    {
        return TagsColumn::make($name)
            ->toggleable(isToggledHiddenByDefault:false)
            ->separator(',')
            ->badge()
            ->searchable();
    }

    public static function image($name)
    {
        return ImageColumn::make($name)
            ->toggleable(isToggledHiddenByDefault:false)
            ->circular()
            ->extraHeaderAttributes([
                'class' => 'w-8' // fix table column width
            ]);
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
