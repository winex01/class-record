<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\DateTimeColumn;

class TasksTable
{
    public static function getColumns()
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
