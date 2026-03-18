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
    protected bool $hasTransmutedGrade;
    protected int $lastColIndex;
    protected string $lastColLetter;
    protected string $initialGradeColLetter;
    protected array $columns = [];
    protected $hasGradeColumn = false;
    protected $hasTransmutedColumn = false;

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
        protected Grade $grade,
    ) {
        $this->students           = $schoolClass->students()->get();
        $this->gradeService       = new GradeComputationService($grade);
        $this->hasTransmutedGrade = $schoolClass->gradeTransmutations()->exists();
        $this->columns = $data['grade_columns'];

        $assessmentsByComponent = $this->gradeService->assessmentsByComponent();
        $totalCols = 0;
        foreach ($assessmentsByComponent as $assessments) {
            $totalCols += $assessments->count() + 3;
        }

        if (in_array('initial_grade', $this->columns) || in_array('grade', $this->columns)) {
            $this->hasGradeColumn = true;
            $totalCols++;
        }

        if ($this->hasTransmutedGrade && in_array('transmuted_grade', $this->columns)) {
            $this->hasTransmutedColumn = true;
            $totalCols++;
        }

        $this->lastColIndex           = 2 + $totalCols;
        $this->lastColLetter          = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->lastColIndex);
        $this->initialGradeColLetter  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->lastColIndex - ($this->hasTransmutedGrade ? 1 : 0));
    }

    public function title(): string
    {
        return $this->grade->grading_period;
    }

    public function collection() { return collect([]); }

    public function styles(Worksheet $sheet) { return []; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('F59E0B');
                $sheet = $event->sheet->getDelegate();

                $this->buildHeaders($sheet);
                $this->buildContent($sheet);
                $this->buildStyles($sheet);
            },
        ];
    }

    protected function buildHeaders($sheet): void
    {
        $assessmentsByComponent = $this->gradeService->assessmentsByComponent();
        $componentSummary       = $this->gradeService->componentSummary();

        // ── # and Student Name ───────────────────────────────────────────
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

        // ── ROW 1: Info row ──────────────────────────────────────────────
        $totalCols    = $this->lastColIndex - 2;
        $sectionWidth = (int) ceil($totalCols / 3);
        $col1Start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3);
        $col1End   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $sectionWidth);
        $col2Start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $sectionWidth);
        $col2End   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $sectionWidth * 2);
        $col3Start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $sectionWidth * 2);

        $sheet->setCellValue("{$col1Start}1", 'Grading Period: ' . $this->grade->grading_period);
        $sheet->mergeCells("{$col1Start}1:{$col1End}1");
        $sheet->getStyle("{$col1Start}1")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]]);

        $sheet->setCellValue("{$col2Start}1", 'Subject: ' . $this->schoolClass->name);
        $sheet->mergeCells("{$col2Start}1:{$col2End}1");
        $sheet->getStyle("{$col2Start}1")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]]);

        $sheet->setCellValue("{$col3Start}1", 'Year & Section: ' . str_replace(',', ', ', $this->schoolClass->year_section ?? ''));
        $sheet->mergeCells("{$col3Start}1:{$this->lastColLetter}1");
        $sheet->getStyle("{$col3Start}1")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]]);

        // ── ROW 2: Component headers ─────────────────────────────────────
        $col = 3;
        foreach ($assessmentsByComponent as $gradingComponentId => $assessments) {
            $colspan   = $assessments->count() + 3;
            $startCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '2';
            $endCell   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + $colspan - 1) . '2';
            $label     = $componentSummary[$gradingComponentId]['component_label']
                . ' (' . $componentSummary[$gradingComponentId]['weighted_score_label'] . ')';

            $sheet->setCellValue($startCell, $label);
            $sheet->mergeCells("{$startCell}:{$endCell}");
            $col += $colspan;
        }

        if ($this->hasGradeColumn) {
            $initialColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$initialColLetter}2", $this->hasTransmutedGrade ? 'Initial Grade' : 'Grade');
            $sheet->mergeCells("{$initialColLetter}2:{$initialColLetter}4");
            $sheet->getStyle("{$initialColLetter}2")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
            $col++;
        }

        if ($this->hasTransmutedColumn) {
            $transmutedColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$transmutedColLetter}2", 'Transmuted Grade');
            $sheet->mergeCells("{$transmutedColLetter}2:{$transmutedColLetter}4");
            $sheet->getStyle("{$transmutedColLetter}2")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]]);
        }

        // ── ROW 3: Assessment numbers + TS PS WS ────────────────────────
        $col = 3;
        foreach ($assessmentsByComponent as $assessments) {
            $num = 1;
            foreach ($assessments as $assessment) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3', $num++);
                $sheet->getComment(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3')
                    ->getText()->createTextRun($assessment->name);
                $col++;
            }
            foreach (['TS' => 'Total Score', 'PS' => 'Percentage Score', 'WS' => 'Weighted Score'] as $key => $label) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3', $key);
                $sheet->getComment(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3')
                    ->getText()->createTextRun($label);
                $col++;
            }
        }

        // ── ROW 4: Highest Possible Score ───────────────────────────────
        $col = 3;
        $sheet->setCellValue('B4', 'Highest Possible Score');
        foreach ($assessmentsByComponent as $gradingComponentId => $assessments) {
            foreach ($assessments as $assessment) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4', $assessment->max_score);
                $col++;
            }
            $meta = $componentSummary[$gradingComponentId];
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '4', $meta['total_score']);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '4', 100);

            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4';
            $sheet->setCellValue($cell, $meta['weighted_score'] / 100);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE);
            $col++;
        }
    }

    protected function buildStyles($sheet): void
    {
        // center align rows 2, 3, 4 except A and B
        $sheet->getStyle("C:{$this->lastColLetter}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // auto size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension($this->initialGradeColLetter)->setAutoSize(true);

        if ($this->hasTransmutedGrade) {
            $sheet->getColumnDimension($this->lastColLetter)->setAutoSize(true);
        }
    }

    protected function buildContent($sheet): void
    {
        foreach ($this->students as $index => $student) {
            $studentRowNum = $index + 2;
            $thisRowNum    = $index + 5;

            $sheet->setCellValue("A{$thisRowNum}", "=Students!A{$studentRowNum}");
            $sheet->setCellValue("B{$thisRowNum}", "=Students!B{$studentRowNum}");

            $col = 'C'; // init and reset to col to C every student
            foreach ($this->gradeService->assessmentsByComponent() as $assessments) {
                $componentStartCol = $col; // start column of every component
                $componentLastCol = null;
                foreach ($assessments as $assessment) {
                    $assessmentStudent = $assessment->students
                        ->firstWhere('id', $student->id);

                    if ($assessmentStudent) {
                        $score = $assessmentStudent->pivot->score ?? null;
                        $sheet->setCellValue("{$col}{$thisRowNum}", $score);
                        $componentLastCol = $col;
                        $col++;
                    }
                }

                if ($componentLastCol !== null) {
                    // $this->gradeService->totalScore
                    $sheet->setCellValue("{$col}{$thisRowNum}", "=SUM({$componentStartCol}{$thisRowNum}:{$componentLastCol}{$thisRowNum})");
                }
                $totalScoreCol = $col;
                $col++;

                // $this->gradeService->percentageScore
                $sheet->setCellValue("{$col}{$thisRowNum}", "=ROUND(({$totalScoreCol}{$thisRowNum}/{$totalScoreCol}4)*{$col}4, 2)");
                $percentageScoreCol = $col;
                $col++;

                // $this->gradeService->weightedScore
                $sheet->setCellValue("{$col}{$thisRowNum}", "=ROUND({$percentageScoreCol}{$thisRowNum}*{$col}4, 2)");
                $col++;

            }// end foreach $this->gradeService->assessmentsByComponent()


            if ($this->hasGradeColumn) {
                // TODO:: grade or initial_grade

            }

            if ($this->hasTransmutedColumn) {
                // TODO:: transmuted_grade
            }

            $thisRowNum++; // incrase row every student
        }
    }// end buildContent
}
