<?php

namespace App\Services;

use App\Filament\Columns\TextColumn;

final class Column
{
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
}
