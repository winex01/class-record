<?php

namespace App\Filament\Exports;

use App\Models\Attendance;
use Illuminate\Support\Number;
use Filament\Actions\Exports\Models\Export;
use App\Filament\Exports\SchoolClassBaseExporter;

class SchoolClassAttendanceExporter extends SchoolClassBaseExporter
{
    public static function getColumns(): array
    {

        return [
            ...SchoolClassStudentsExporter::getColumns(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your school class attendance export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

}
