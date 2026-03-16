<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchoolClassExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
        ])->only($data['columns'])->all();
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
                    'startColor' => ['rgb' => '10B981'], // emerald-500
                ],
            ],
        ];
    }
}
