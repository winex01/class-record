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

class AttendanceSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $columnMap;
    protected $attendances;
    protected $students;

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {
        $this->students    = $schoolClass->students()->get();
        $this->attendances = $schoolClass->attendances()->orderBy('date')->get();

        $dateCount = count($this->attendances);
        $endCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $dateCount);
        $hasDates = in_array('dates', $data['attendance_columns']);

        $this->columnMap = collect([
            'dates'   => ['label' => 'dates', 'dynamic' => true],
            'present' => [
                'label'   => 'Present',
                'formula' => fn ($rowNum, $student) => $hasDates
                    ? ($dateCount === 0 ? 0 : "=COUNTIF(C{$rowNum}:{$endCol}{$rowNum},\"✓\")")
                    : ($this->attendances->filter(fn ($a) => $a->students->firstWhere('id', $student->id)?->pivot->present == true)->count() ?: "0"),
            ],
            'absent'  => [
                'label'   => 'Absent',
                'formula' => fn ($rowNum, $student) => $hasDates
                    ? ($dateCount === 0 ? 0 : "=COUNTIF(C{$rowNum}:{$endCol}{$rowNum},\"✗\")")
                    : ($this->attendances->filter(fn ($a) => $a->students->firstWhere('id', $student->id)?->pivot->present == false)->count() ?: "0"),
            ],
        ])->only($data['attendance_columns'])->all();
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function collection()
    {
        return $this->students->map(function ($student, $index) {
            $rowNum = $index + 2;

            $row = [
                '#'            => "=Students!A{$rowNum}",
                'Student Name' => "=Students!B{$rowNum}",
            ];

            foreach ($this->columnMap as $key => $col) {
                if ($key === 'dates') {
                    foreach ($this->attendances as $attendance) {
                        $present = $attendance->students
                            ->firstWhere('id', $student->id)
                            ?->pivot->present;

                        $row[$attendance->date->format('M d')] = $present ? '✓' : '✗';
                    }
                } else {
                    $row[$col['label']] = ($col['formula'])($rowNum, $student);
                }
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $headings = ['#', 'Student Name'];

        foreach ($this->columnMap as $key => $col) {
            if ($key === 'dates') {
                foreach ($this->attendances as $attendance) {
                    $headings[] = $attendance->date->format('M d,') . "\n" . $attendance->date->format('Y');
                }
            } else {
                $headings[] = $col['label'];
            }
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getAlignment()->setWrapText(true);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('2563EB');
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $startColIndex = 3;
                $endColIndex = 2 ;

                if (isset($this->columnMap['dates'])) {
                    $endColIndex += count($this->attendances);
                    for ($row = 2; $row <= $highestRow; $row++) {
                        for ($colIndex = $startColIndex; $colIndex <= $endColIndex; $colIndex++) {
                            $colLetter  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                            $validation = $sheet->getCell("{$colLetter}{$row}")->getDataValidation();
                            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                            $validation->setAllowBlank(false);
                            $validation->setShowDropDown(true);
                            $validation->setShowInputMessage(true);
                            $validation->setShowErrorMessage(true);
                            $validation->setFormula1('"✓,✗"');
                        }
                    }
                }

                $endColIndex++;

                if (isset($this->columnMap['present'])) {
                    $presentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColIndex);
                    $sheet->getStyle("{$presentCol}2:{$presentCol}{$highestRow}")
                        ->getFont()->setSize(13)->getColor()->setARGB('10B981');
                    $endColIndex++;
                }

                if (isset($this->columnMap['absent'])) {
                    $absentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColIndex);
                    $sheet->getStyle("{$absentCol}2:{$absentCol}{$highestRow}")
                        ->getFont()->setSize(13)->getColor()->setARGB('DC2626');
                }
            },
        ];
    }
}
