<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Filament\Columns\TextColumn;
use App\Filament\Columns\BooleanColumn;
use Filament\Tables\Columns\Layout\Split;

class SchoolClassColumns
{
    public static function schema()
    {
        return [
            Split::make([
                TextColumn::make('name')
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(year_section) LIKE ?', ['%' . strtolower($search) . '%']);
                        });
                    })
                    ->description(fn ($record) => $record->year_section),

                BooleanColumn::make('active')
                    ->trueLabel('Active')
                    ->falseLabel('Archived')
                    ->falseIcon('heroicon-o-archive-box')
                    ->falseColor('warning')
                    ->description(function ($record) {
                        if (!$record->date_start && !$record->date_end) {
                            return 'No dates set';
                        }
                        return ($record->date_start?->format('M d, Y') ?? 'N/A') . ' → ' . ($record->date_end?->format('M d, Y') ?? 'N/A');
                    })
                    ->searchable(
                        query: fn ($query, string $search) => $query
                            ->whereRaw("DATE_FORMAT(date_start, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("DATE_FORMAT(date_end, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                    ),
            ])
        ];
    }
}
