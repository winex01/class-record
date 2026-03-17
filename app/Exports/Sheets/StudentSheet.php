<?php

namespace App\Exports\Sheets;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithTitle
{
    protected array $columnMap;

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {
        $this->columnMap = collect([
            'full_name'      => ['label' => 'Student Name', 'value' => fn ($s) => $s->full_name],
            'gender'         => ['label' => 'Gender',       'value' => fn ($s) => $s->gender?->getLabel()],
            'birth_date'     => ['label' => 'Birth Date',   'value' => fn ($s) => $s->birth_date?->format('M d, Y')],
            'email'          => ['label' => 'Email',        'value' => fn ($s) => $s->email],
            'contact_number' => ['label' => 'Contact',      'value' => fn ($s) => $s->contact_number],
        ])->only($data['student_columns'])->all();
    }

    public function title(): string
    {
        return 'Students';
    }

    public function collection()
    {
        return $this->schoolClass->students()
            ->get()
            ->map(function ($student, $index) {
                $row = ['#' => $index + 1];

                foreach ($this->columnMap as $col) {
                    $row[$col['label']] = ($col['value'])($student);
                }

                return $row;
            });
    }

    public function headings(): array
    {
        return ['#', ...collect($this->columnMap)->pluck('label')->all()];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('10B981'); // emerald
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $headings = $this->headings();
                $genderCol = array_search('Gender', $headings);

                if ($genderCol === false) return;

                $genderColLetter = chr(ord('A') + $genderCol);

                for ($row = 2; $row <= $highestRow; $row++) {
                    $value = $sheet->getCell("{$genderColLetter}{$row}")->getValue();

                    $color = match ($value) {
                        'Male'   => '3B82F6',
                        'Female' => 'EC4899',
                        default  => '000000',
                    };

                    $sheet->getStyle("{$genderColLetter}{$row}")
                        ->getFont()
                        ->getColor()
                        ->setARGB($color);
                }
            },
        ];
    }
}
