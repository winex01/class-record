<?php

namespace App\Filament\Resources\Notes\Tables;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\DateTimeColumn;

class NotesTable
{
    public static function getColumns()
    {
        return [
            TextColumn::make('note'),
            TagsColumn::make('tags'),
            DateTimeColumn::make('starts_at')->dateTime(),
            DateTimeColumn::make('ends_at')->dateTime(),
        ];
    }
}
