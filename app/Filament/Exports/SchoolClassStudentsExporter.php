<?php

namespace App\Filament\Exports;

use Illuminate\Support\Number;
use OpenSpout\Writer\XLSX\Options;
use App\Models\SchoolClassStudents;
use Filament\Actions\Exports\Exporter;
use Illuminate\Database\Eloquent\Model;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;

class SchoolClassStudentsExporter extends Exporter
{
    protected static ?string $model = SchoolClassStudents::class;

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

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setBackgroundColor(Color::rgb(124, 58, 237)) // violet-600
            ->setFontColor(Color::rgb(255, 255, 255)); // white text
    }

    public function getXlsxCellStyle(): ?Style
    {
        return (new Style())
            ->setFontSize(11);
    }

    public function getXlsxWriterOptions(): ?Options
    {
        $options = new Options();
        $options->setColumnWidth(5, 1);   // # column
        $options->setColumnWidth(30, 2);  // Student Name
        $options->setColumnWidth(12, 3);  // Gender
        $options->setColumnWidth(15, 4);  // Birth Date
        $options->setColumnWidth(25, 5);  // Email
        $options->setColumnWidth(15, 6);  // Contact

        return $options;
    }
}
