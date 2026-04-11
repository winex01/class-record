<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use App\Filament\Columns\TagsColumn;
use App\Filament\Columns\TextColumn;
use App\Filament\Columns\BooleanColumn;

class SchoolClassTable
{
    public static function getColumns()
    {
        return [
            TextColumn::make('name')
                ->description(function ($record) {
                    if (!$record->date_start && !$record->date_end) {
                        return 'No dates set';
                    }
                    return ($record->date_start?->format('M d, Y') ?? 'N/A') . ' → ' . ($record->date_end?->format('M d, Y') ?? 'N/A');
                }),

            TagsColumn::make('year_section')
                ->badge(false)
                ->color('primary'),

            BooleanColumn::make('active')
                ->label('Status')
                ->trueLabel('Active')
                ->falseLabel('Archived')
                ->falseIcon('heroicon-o-archive-box')
                ->falseColor('warning')
                ->searchable(
                    // although the dates are on the  text column name description, there is no harm on adding the search logic here
                    query: function ($query, string $search) {
                        $driver = $query->getConnection()->getDriverName();

                        if ($driver === 'sqlite') {
                            return $query->where(function ($q) use ($search) {
                                $q->whereRaw("strftime('%m/%d/%Y', date_start) LIKE ?", ["%{$search}%"])
                                    ->orWhereRaw("strftime('%m/%d/%Y', date_end) LIKE ?", ["%{$search}%"]);
                            });
                        }

                        return $query->where(function ($q) use ($search) {
                            $q->whereRaw("DATE_FORMAT(date_start, '%b %d, %Y') LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("DATE_FORMAT(date_end, '%b %d, %Y') LIKE ?", ["%{$search}%"]);
                        });
                    }
                )
        ];
    }
}
