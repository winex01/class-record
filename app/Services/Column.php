<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;

final class Column
{
    public static function icon($name)
    {
        return IconColumn::make($name)
            ->wrap()
            ->width('1%')
            ->alignCenter()
            ->trueIcon('heroicon-o-x-circle')
            ->falseIcon('heroicon-o-check-circle')
            ->trueColor(Color::Rose)
            ->falseColor(Color::Emerald);
    }

    public static function timePickerFromAndTo($name, $starts_at = 'starts_at', $ends_at = 'ends_at')
    {
        return TextColumn::make($name)
            ->wrap()
            ->formatStateUsing(function ($state) use ($starts_at, $ends_at) {
                if (!is_array($state)) {
                    return '-';
                }

                $start = isset($state[$starts_at])
                    ? Carbon::parse($state[$starts_at])->format('g:i a')
                    : '-';

                $end = isset($state[$ends_at])
                    ? Carbon::parse($state[$ends_at])->format('g:i a')
                    : '-';

                return "{$start} - {$end}";
            });
    }

    public static function amount($name)
    {
        return static::text($name)
            ->wrap()
            ->prefix('₱')
            ->width('1%')
            ->color('primary')
            ->formatStateUsing(fn ($state) => number_format($state, 2));
    }

    public static function select($name)
    {
        return SelectColumn::make($name)
            ->placeholder('-')
            ->disablePlaceholderSelection()
            ->sortable()
            ->searchable()
            ->native(false)
            ->width('1%')
            ->extraAttributes(['style' => 'min-width: 130px; ']);
    }

    public static function textInput($name)
    {
        return TextInputColumn::make($name)
            ->sortable()
            ->searchable()
            ->extraAttributes(['style' => 'min-width: 120px; max-width: 120px;']);
    }

    public static function text($name)
    {
        return TextColumn::make($name)
            ->wrap()
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable()
            ->searchable();
    }

    public static function date($name)
    {
        return static::text($name)
            ->wrap()
            ->date()
            ->searchable(
                query: fn ($query, string $search) =>
                    $query->whereRaw(
                        "DATE_FORMAT({$name}, '%b %d, %Y') LIKE ?",
                        ["%{$search}%"]
                    )
            );
    }

    public static function timestamp($name)
    {
        return static::text($name)
            ->wrap()
            ->dateTime()
            ->tooltip(fn ($record) => ('Search: '.$record->{$name}));
    }

    public static function boolean(
        string $name,
        string $trueLabel = 'Yes',
        string $falseLabel = 'No',
        string $trueIcon = 'heroicon-o-check',
        string $falseIcon = 'heroicon-o-x-mark',
        string $trueColor = 'success',
        string $falseColor = 'danger',
        string $trueDesc = null,
        string $falseDesc = null,
    ): TextColumn {
        return TextColumn::make($name)
            ->toggleable(isToggledHiddenByDefault: false)
            ->width('1%')
            ->formatStateUsing(fn($state) => $state ? $trueLabel : $falseLabel)
            ->icon(fn($state) => $state ? $trueIcon : $falseIcon)
            ->color(fn($state) => $state ? $trueColor : $falseColor)
            ->description(fn($state) => $state ? $trueDesc : $falseDesc)
            ->badge();
    }

    public static function tags($name = 'tags')
    {
        return TagsColumn::make($name)
            ->wrap()
            ->toggleable(isToggledHiddenByDefault:false)
            ->separator(',')
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
            ->wrap()
            ->toggleable(isToggledHiddenByDefault: false)
            ->sortable()
            ->searchable()
            ->label(Str::headline($attribute))
            ->badge()
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
