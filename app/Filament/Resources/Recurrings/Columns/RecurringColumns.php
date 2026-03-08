<?php

namespace App\Filament\Resources\Recurrings\Columns;

use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;

class RecurringColumns
{
    public static function schema()
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('description'),
            TagsColumn::make('tags'),
            DateColumn::make('date_start'),
            DateColumn::make('date_end'),
        ];
    }
}
