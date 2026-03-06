<?php

namespace App\Filament\Resources\GradeComponentTemplates\Columns;

use App\Filament\Columns\TextColumn;

class GradeComponentTemplateColumns
{
    public static function schema(): array
    {
        return [
            TextColumn::make('name'),

            TextColumn::make('components')
                ->listWithLineBreaks()
                ->formatStateUsing(fn ($state) =>
                    "{$state['name']} ({$state['weighted_score']}%)"
                )
                ->searchable(query: function ($query, string $search) {
                    $query->whereRaw(
                        "LOWER(JSON_EXTRACT(components, '$')) LIKE ?",
                        ['%' . strtolower($search) . '%']
                    );
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
