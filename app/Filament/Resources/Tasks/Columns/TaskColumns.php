<?php

namespace App\Filament\Resources\Tasks\Columns;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\DateTimeColumn;

class TaskColumns
{
    public static function schema()
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('description'),
            TagsColumn::make('tags'),
            DateTimeColumn::make('starts_at')->dateTime(),
            DateTimeColumn::make('ends_at')->dateTime(),
        ];
    }
}
