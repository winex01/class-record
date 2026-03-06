<?php

namespace App\Filament\Resources\Notes\Columns;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\DateTimeColumn;

class NoteColumns
{
    public static function schema()
    {
        return [
            TextColumn::make('note'),
            TagsColumn::make('tags'),
            DateTimeColumn::make('starts_at')->dateTime(),
            DateTimeColumn::make('ends_at')->dateTime(),
        ];
    }
}
