<?php

namespace App\Filament\Exports;

use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use App\Filament\Exports\SchoolClassBaseExporter;

class SchoolClassStudentsExporter extends SchoolClassBaseExporter
{
    public static function getColumns(): array
    {
        $rowNumber = 0;

        return [
            ExportColumn::make('index')
                ->label('#')
                ->state(static function ($record) use (&$rowNumber): int {
                    return ++$rowNumber;
                }),

            ExportColumn::make('full_name')
                ->label('Student Name')
                ->state(fn ($record) => $record->full_name),

            ExportColumn::make('gender'),

            ExportColumn::make('birth_date')
                ->label('Birth Date')
                ->state(fn (Model $record) => $record->birth_date?->format('M d, Y'))
                ->enabledByDefault(false),

            ExportColumn::make('email')->enabledByDefault(false),
            ExportColumn::make('contact_number')->label('Contact')->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your school class students export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
