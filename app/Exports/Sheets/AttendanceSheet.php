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
    protected $attendances;
    protected $students;

    public function __construct(
        protected SchoolClass $schoolClass,
    ) {
        $this->students = $schoolClass->students()->get();
        $this->attendances = $schoolClass->attendances()->orderBy('date')->get();
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function collection()
    {
        $dateCount = count($this->attendances);
        $endCol = chr(ord('A') + 1 + $dateCount);

        return $this->students->map(function ($student, $index) use ($dateCount, $endCol) {
            $rowNum = $index + 2;

            $row = [
                '#'            => "=Students!A{$rowNum}",
                'Student Name' => "=Students!B{$rowNum}",
            ];

            foreach ($this->attendances as $attendance) {
                $present = $attendance->students
                    ->firstWhere('id', $student->id)
                    ?->pivot->present;

                $row[$attendance->date->format('M d')] = $present ? 'P' : 'A';
            }

            $row['Present'] = "=COUNTIF(C{$rowNum}:{$endCol}{$rowNum},\"P\")";
            $row['Absent']  = "=COUNTIF(C{$rowNum}:{$endCol}{$rowNum},\"A\")";

            return $row;
        });
    }

    public function headings(): array
    {
        $headings = ['#', 'Student Name'];

        foreach ($this->attendances as $attendance) {
            $headings[] = $attendance->date->format('M d,') . "\n" . $attendance->date->format('Y');
        }

        $headings[] = 'Present';
        $headings[] = 'Absent';

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
                    'startColor' => ['rgb' => '2563EB'], // info
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('2563EB'); // info
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $dateCount = count($this->attendances);

                $startColIndex = 3; // C
                $endColIndex = 2 + $dateCount;

                for ($row = 2; $row <= $highestRow; $row++) {
                    // data validation dropdown for P/A columns
                    for ($colIndex = $startColIndex; $colIndex <= $endColIndex; $colIndex++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

                        $validation = $sheet->getCell("{$colLetter}{$row}")->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowDropDown(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setFormula1('"P,A"');
                    }

                    // Present column — emerald, bigger font
                    $presentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColIndex + 1);
                    $sheet->getStyle("{$presentCol}2:{$presentCol}{$highestRow}")
                        ->getFont()
                        ->setSize(13)
                        ->getColor()
                        ->setARGB('10B981');

                    // Absent column — red, bigger font
                    $absentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColIndex + 2);
                    $sheet->getStyle("{$absentCol}2:{$absentCol}{$highestRow}")
                        ->getFont()
                        ->setSize(13)
                        ->getColor()
                        ->setARGB('DC2626');
                }
            },
        ];
    }
}
