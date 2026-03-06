<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\BooleanColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class SchoolClassColumns
{
    public static function schema()
    {
        return [
            Stack::make([
                Split::make([
                    TextColumn::make('name')
                        ->label('Subject')
                        ->searchable(
                            query: fn ($query, string $search) => $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(date_start, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("DATE_FORMAT(date_end, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                        )
                        ->description(function ($record) {
                            if (!$record->date_start && !$record->date_end) {
                                return 'No dates set';
                            }
                            return ($record->date_start?->format('M d, Y') ?? 'N/A') . ' → ' . ($record->date_end?->format('M d, Y') ?? 'N/A');
                        }),

                    BooleanColumn::make('active')
                        ->trueLabel('Active')
                        ->falseLabel('Archived')
                        ->falseIcon('heroicon-o-lock-closed')
                        ->falseColor('warning')
                        ->grow(false)

                ]),

                TagsColumn::make('year_section')
                    ->color('primary')
                    ->badge(false)
            ])
        ];
    }
}
