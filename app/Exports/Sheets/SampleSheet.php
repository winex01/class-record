<?php

namespace App\Exports\Sheets;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SampleSheet implements FromCollection, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {
        //
    }

    public function title(): string
    {
        return 'Lessons';
    }

    public function collection()
    {
        return collect([]);
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('FFede9fe');
                $sheet = $event->sheet->getDelegate();

                $this->buildHeaders($sheet);
                $this->buildContent($sheet);
                $this->buildStyles($sheet);
            },
        ];
    }

    protected function buildHeaders($sheet): void
    {
        //
    }

    protected function buildContent($sheet): void
    {
        //
    }

    protected function buildStyles($sheet): void
    {
        //
    }
}
