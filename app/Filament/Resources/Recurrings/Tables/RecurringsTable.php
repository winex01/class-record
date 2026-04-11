<?php

namespace App\Filament\Resources\Recurrings\Tables;

use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;

class RecurringsTable
{
    public static function getColumns()
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
