<?php

namespace App\Services;

use Illuminate\Support\Str;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

final class Column
{
    public static function text($name)
    {
        return TextColumn::make($name)
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable()
            ->searchable();
    }

    public static function boolean(
        string $name,
        string $trueLabel = 'Yes',
        string $falseLabel = 'No',
        string $trueIcon = 'heroicon-o-check',
        string $falseIcon = 'heroicon-o-x-mark',
        string $trueColor = 'success',
        string $falseColor = 'danger',
    ): TextColumn {
        return TextColumn::make($name)
            ->toggleable(isToggledHiddenByDefault: false)
            ->width('1%')
            ->formatStateUsing(fn($state) => $state ? $trueLabel : $falseLabel)
            ->icon(fn($state) => $state ? $trueIcon : $falseIcon)
            ->color(fn($state) => $state ? $trueColor : $falseColor)
            ->badge();
    }

    public static function tags($name = 'tags')
    {
        return TagsColumn::make($name)
            ->toggleable(isToggledHiddenByDefault:false)
            ->separator(',')
            ->badge()
            ->searchable(query: function (Builder $query, string $search) use ($name): Builder {
                return $query->whereRaw('LOWER('. $name .') LIKE ?', ['%' . strtolower($search) . '%']);
            });
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
            ->icon(function ($state) use ($enum) {
                if (is_object($state) && method_exists($state, 'getIcon')) {
                    return $state->getIcon();
                }

                $enumInstance = $enum::tryFrom($state);
                return $enumInstance && method_exists($enumInstance, 'getIcon')
                    ? $enumInstance->getIcon()
                    : null;
            })
            ->color(function ($state) use ($enum) {
                if (is_object($state) && method_exists($state, 'getColor')) {
                    return $state->getColor();
                }

                $enumInstance = $enum::tryFrom($state);
                return $enumInstance && method_exists($enumInstance, 'getColor')
                    ? $enumInstance->getColor()
                    : null;
            })
            ->getStateUsing(fn ($record) => $record->{$attribute})
            ->formatStateUsing(function ($state) use ($enum) {
                if (is_object($state) && method_exists($state, 'getLabel')) {
                    return $state->getLabel();
                }

                $enumInstance = $enum::tryFrom($state);
                return $enumInstance && method_exists($enumInstance, 'getLabel')
                    ? $enumInstance->getLabel()
                    : ($enumInstance ? $enumInstance->name : $state);
            });
    }
}
