<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use Illuminate\Support\Carbon;
use App\Filament\Fields\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassAttendanceFilters
{
    public static function dateRange()
    {
        return
            Filter::make('date')
            ->schema([
                DatePicker::make('date_from')
                    ->label('From'),
                DatePicker::make('date_to')
                    ->label('To'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                    ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['date_from'] ?? null) {
                    $indicators[] = 'From: ' . Carbon::parse($data['date_from'])->format('M j, Y');
                }

                if ($data['date_to'] ?? null) {
                    $indicators[] = 'To: ' . Carbon::parse($data['date_to'])->format('M j, Y');
                }

                return $indicators;
            })
            ->columnSpan(2);
    }
}
