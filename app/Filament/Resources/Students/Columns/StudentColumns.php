<?php

namespace App\Filament\Resources\Students\Columns;

use App\Enums\Gender;
use App\Filament\Columns\DateColumn;
use App\Filament\Columns\EnumColumn;
use App\Filament\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Students\StudentResource;

class StudentColumns
{
    public static function schema()
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

            EnumColumn::make('gender')->enum(Gender::class),

            DateColumn::make('birth_date'),

            TextColumn::make('email')
                ->copyable() // copyable only work if SSL is enabled. for localhost test "herd secure class-record"
                ->copyMessage('Email Copied!')
                ->copyMessageDuration(1500),

            'contact_number' =>
            TextColumn::make('contact_number')
                ->label('Contact')
                ->copyable() // copyable only work if SSL is enabled. for localhost test "herd secure class-record"
                ->copyMessage('Contact Copied!')
                ->copyMessageDuration(1500),
        ];
    }
}
