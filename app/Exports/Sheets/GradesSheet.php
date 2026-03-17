<?php

namespace App\Exports\Sheets;

use App\Models\Grade;
use App\Models\SchoolClass;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Services\GradeComputationService;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradesSheet implements FromCollection, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected $students;
    protected GradeComputationService $gradeService;

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
        protected Grade $grade,
    ) {
        $this->students     = $schoolClass->students()->get();
        $this->gradeService = new GradeComputationService($grade);
    }

    public function title(): string
    {
        return $this->grade->grading_period;
    }

    public function collection()
    {
        return collect([]); // empty — we write manually in AfterSheet
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('F59E0B');
                $sheet = $event->sheet->getDelegate();
                $this->buildHeaders($sheet);
            },
        ];
    }

    protected function buildHeaders($sheet): void
    {
        $assessmentsByComponent = $this->gradeService->assessmentsByComponent();
        $componentSummary       = $this->gradeService->componentSummary();
        $hasTransmutedGrade     = $this->schoolClass->gradeTransmutations()->exists();

        // calculate total cols excluding A (#) and B (Student Name)
        $totalCols = 0;
        foreach ($assessmentsByComponent as $assessments) {
            $totalCols += $assessments->count() + 3;
        }
        $totalCols += 1; // Initial Grade
        if ($hasTransmutedGrade) $totalCols += 1;

        $lastColIndex  = 2 + $totalCols; // A=1, B=2, components start at C=3
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

        // ── merge A1:A3 for # and B1:B4 for Student Name ─────────────────
        $sheet->mergeCells('A1:A3');
        $sheet->setCellValue('A1', '#');
        $sheet->getStyle('A1')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font'      => ['bold' => true],
        ]);

        $sheet->mergeCells('B1:B3');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->getStyle('B1')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font'      => ['bold' => true],
        ]);

        // ── ROW 1: Info row starts from C ────────────────────────────────
        $sectionWidth = (int) ceil($totalCols / 3);

        $col1Start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3);
        $col1End   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $sectionWidth);
        $col2Start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $sectionWidth);
        $col2End   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $sectionWidth * 2);
        $col3Start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $sectionWidth * 2);
        $col3End   = $lastColLetter;

        $sheet->setCellValue("{$col1Start}1", 'Grading Period: ' . $this->grade->grading_period);
        $sheet->mergeCells("{$col1Start}1:{$col1End}1");
        $sheet->getStyle("{$col1Start}1")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font'      => ['bold' => true],
        ]);

        $sheet->setCellValue("{$col2Start}1", 'Subject: ' . $this->schoolClass->name);
        $sheet->mergeCells("{$col2Start}1:{$col2End}1");
        $sheet->getStyle("{$col2Start}1")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font'      => ['bold' => true],
        ]);

        $sheet->setCellValue("{$col3Start}1", 'Year & Section: ' . str_replace(',', ', ', $this->schoolClass->year_section ?? ''));
        $sheet->mergeCells("{$col3Start}1:{$col3End}1");
        $sheet->getStyle("{$col3Start}1")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font'      => ['bold' => true],
        ]);

        // ── ROW 2: Component headers starts from C ───────────────────────
        $col = 3;
        foreach ($assessmentsByComponent as $gradingComponentId => $assessments) {
            $colspan   = $assessments->count() + 3;
            $startCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '2';
            $endCell   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + $colspan - 1) . '2';

            $label = $componentSummary[$gradingComponentId]['component_label']
                . ' (' . $componentSummary[$gradingComponentId]['weighted_score_label'] . ')';

            $sheet->setCellValue($startCell, $label);
            $sheet->mergeCells("{$startCell}:{$endCell}");

            $col += $colspan;
        }

        $initialColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $initialCol       = $initialColLetter . '2';
        $sheet->setCellValue($initialCol, $hasTransmutedGrade ? 'Initial Grade' : 'Grade');
        $sheet->mergeCells("{$initialColLetter}2:{$initialColLetter}4");
        $sheet->getStyle($initialCol)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $col++;

        if ($hasTransmutedGrade) {
            $transmutedColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $transmutedCol       = $transmutedColLetter . '2';
            $sheet->setCellValue($transmutedCol, 'Transmuted Grade');
            $sheet->mergeCells("{$transmutedColLetter}2:{$transmutedColLetter}4");
            $sheet->getStyle($transmutedCol)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // ── ROW 3: Assessment numbers + TS PS WS starts from C ──────────
        $col = 3;
        foreach ($assessmentsByComponent as $assessments) {
            $num = 1;
            foreach ($assessments as $assessment) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3';
                $sheet->setCellValue($cell, $num++);
                $col++;
            }
            foreach (['TS', 'PS', 'WS'] as $label) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3';
                $sheet->setCellValue($cell, $label);
                $col++;
            }
        }

        // ── ROW 4: Highest Possible Score starts from C ──────────────────
        $col = 3;
        $sheet->setCellValue('B4', 'Highest Possible Score');
        foreach ($assessmentsByComponent as $gradingComponentId => $assessments) {
            foreach ($assessments as $assessment) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4';
                $sheet->setCellValue($cell, $assessment->max_score);
                $col++;
            }
            $meta = $componentSummary[$gradingComponentId];
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4', $meta['total_score']); $col++;
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4', 100); $col++;
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4', $meta['weighted_score_label'] ?? '-'); $col++;
        }

        // ── Student rows starting at row 5 ──────────────────────────────
        foreach ($this->students as $index => $student) {
            $studentRowNum = $index + 2; // Students sheet starts at row 2
            $thisRowNum    = $index + 5; // This sheet starts at row 5

            $sheet->setCellValue("A{$thisRowNum}", "=Students!A{$studentRowNum}");
            $sheet->setCellValue("B{$thisRowNum}", "=Students!B{$studentRowNum}");
        }

        // ── center align rows 2, 3, 4 except A and B ────────────────────
        $sheet->getStyle("C2:{$lastColLetter}4")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ── auto size columns ────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        $initialGradeColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex - ($hasTransmutedGrade ? 1 : 0));
        $sheet->getColumnDimension($initialGradeColLetter)->setAutoSize(true);

        if ($hasTransmutedGrade) {
            $sheet->getColumnDimension($lastColLetter)->setAutoSize(true);
        }
    }
}
