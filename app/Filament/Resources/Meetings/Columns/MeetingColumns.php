<?php

namespace App\Filament\Resources\Meetings\Columns;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\DateTimeColumn;

class MeetingColumns
{
    public static function schema()
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('description'),
            TagsColumn::make('tags'),
            DateTimeColumn::make('starts_at'),
            DateTimeColumn::make('ends_at'),
        ];
    }
}
