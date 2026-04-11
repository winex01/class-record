<?php

namespace App\Filament\Resources\Students\Tables;

use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\ImageColumn;
use App\Filament\Resources\Students\StudentResource;

class StudentsTable
{
    public static function getColumns()
    {
        return [
            ImageColumn::make('photo'),
            TextColumn::make('full_name')
                ->tooltip(fn ($record) => $record->complete_name)
                ->sortable(query: function ($query, $direction) {
                    $callback = StudentResource::defaultNameSort($direction);
                    $callback($query);
                })
                ->searchable(['last_name', 'first_name', 'middle_name', 'suffix_name']),
            TextColumn::make('gender'),
            DateColumn::make('birth_date'),
            TextColumn::make('email')
                ->localCopyable(),
            'contact_number' =>
            TextColumn::make('contact_number')
                ->label('Contact')
                ->localCopyable()
        ];
    }
}
