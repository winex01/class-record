<?php

namespace App\Exports\Sheets;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Services\GradeComputationService;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinalGradesSheet implements FromCollection, ShouldAutoSize, WithTitle, WithEvents
{
    protected $grades;
    protected $students;
    protected $gradeServices;

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {
        $this->grades = $this->schoolClass->grades()
            ->orderBy('sort', 'asc')
            ->get()
            ->filter(fn($grade) => $grade->is_complete);

        $this->students = $this->schoolClass->students()->get();

        $this->gradeServices = $this->grades->mapWithKeys(fn($grade) => [
            $grade->grading_period => new GradeComputationService($grade)
        ]);
    }

    public function title(): string
    {
        return 'Final Grades';
    }

    public function collection()
    {
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('FF0F766E');
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('C2');

                $this->buildHeaders($sheet);
                $this->buildContent($sheet);
                $this->buildStyles($sheet);
            },
        ];
    }

    protected function buildHeaders($sheet): void
    {
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Student Name');

        $col = 3;

        if (in_array('grading_period', $this->data['final_grade_columns'])) {
            foreach ($this->grades as $grade) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}1", $grade->grading_period);
                $col++;
            }
        }

        if (in_array('final_grade', $this->data['final_grade_columns'])) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}1", 'Final Grade');
        }
    }

    protected function buildContent($sheet): void
    {
        foreach ($this->students as $index => $student) {
            $rowNum = $index + 2;

            $sheet->setCellValue("A{$rowNum}", '=' . StudentsSheet::getTitle() . "!A{$rowNum}");
            $sheet->setCellValue("B{$rowNum}", '=' . StudentsSheet::getTitle() . "!B{$rowNum}");

            $col = 3;

            if (in_array('grading_period', $this->data['final_grade_columns'])) {
                foreach ($this->grades as $grade) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->setCellValue(
                        "{$colLetter}{$rowNum}",
                        $this->gradeServices->get($grade->grading_period)->gradingPeriodGrade($student->id)
                    );
                    $col++;
                }
            }

            if (in_array('final_grade', $this->data['final_grade_columns'])) {
                $finalGrade = round($this->gradeServices->avg(
                    fn($service) => $service->gradingPeriodGrade($student->id)
                ));

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$rowNum}", $finalGrade);
            }
        }
    }

    protected function buildStyles($sheet): void
    {
        $highestRow = $sheet->getHighestRow();
        $hasGradingPeriod = in_array('grading_period', $this->data['final_grade_columns']);
        $hasFinalGrade = in_array('final_grade', $this->data['final_grade_columns']);

        $totalCols = 2
            + ($hasGradingPeriod ? $this->grades->count() : 0)
            + ($hasFinalGrade ? 1 : 0);

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Header row
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Center all content rows
        $sheet->getStyle("A2:{$lastCol}{$highestRow}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto size all columns
        foreach (range(1, $totalCols) as $colIndex) {
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
        }

        $col = 3;

        if ($hasGradingPeriod) {
            $emeraldCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $this->grades->count());
            $sheet->getStyle("C2:{$emeraldCol}{$highestRow}")
                ->getFont()->getColor()->setARGB('10B981');
            $col += $this->grades->count();
        }

        if ($hasFinalGrade) {
            $finalCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$finalCol}2:{$finalCol}{$highestRow}")
                ->getFont()->getColor()->setARGB('3B82F6');
        }
    }
}
