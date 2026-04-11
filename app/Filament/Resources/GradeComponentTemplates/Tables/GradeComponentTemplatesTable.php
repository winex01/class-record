<?php

namespace App\Filament\Resources\GradeComponentTemplates\Tables;

use App\Filament\Columns\TextColumn;

class GradeComponentTemplatesTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name'),

            TextColumn::make('components')
                ->listWithLineBreaks()
                ->formatStateUsing(
                    fn($state) =>
                    "{$state['name']} ({$state['weighted_score']}%)"
                )
                ->searchable(query: function ($query, string $search) {
                    $query->where('components', 'like', '%' . strtolower($search) . '%');
                })
                ->color(function ($state, $rowLoop) {
                    return match ($rowLoop->iteration % 5) {
                        1 => 'primary',
                        2 => 'info',
                        3 => 'warning',
                        4 => 'pink',
                        default => 'purple',
                    };
                })
        ];
    }
}
