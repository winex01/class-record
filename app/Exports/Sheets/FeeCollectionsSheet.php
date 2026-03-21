<?php

namespace App\Exports\Sheets;

use App\Models\SchoolClass;
use App\Exports\Sheets\StudentsSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class FeeCollectionsSheet implements WithTitle, WithEvents
{
    protected $students;
    protected $feeCollections;
    protected $selectedColumns;
    protected int $rowIndex = 5; // 4 header rows
    protected int $lastCol = 1;

    // stores each fee collection's paid/remaining col letters for SUM
    protected array $paidColLetters = [];
    protected array $remainingColLetters = [];

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {
        $this->students = $schoolClass->students()->get();
        $this->feeCollections = $schoolClass->feeCollections()->with('students')->get();
        $this->selectedColumns = $this->data['fee_collection_columns'] ?? [];
    }

    public function title(): string
    {
        return 'Fee Collections';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('FF6B7280'); // gray-500
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('C5');
                $this->buildHeaders($sheet);
                $this->buildContent($sheet);
                $this->buildStyles($sheet);
            },
        ];
    }

    protected function buildHeaders($sheet): void
    {
        // ── # and Student Name ───────────────────────────────────────────
        $sheet->mergeCells('A1:A4');
        $sheet->setCellValue('A1', '#');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('B1:B4');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->getStyle('B1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $col = 3;

        foreach ($this->feeCollections as $feeCollection) {
            $startCol = $col;
            $subColCount = 0;
            $subColCount++;
            $subColCount++;
            $endCol = $col + $subColCount - 1;

            $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
            $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCol);

            // ── Row 1: Fee Collection name merged ────────────────────────
            $sheet->mergeCells("{$startColLetter}1:{$endColLetter}1");
            $sheet->setCellValue("{$startColLetter}1", $feeCollection->name);
            $sheet->getStyle("{$startColLetter}1")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // ── Row 2: Amount label / Date label ─────────────────────────
            $sheet->setCellValue("{$startColLetter}2", 'Amount');
            $nextCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + 1);
            $sheet->setCellValue("{$nextCol}2", 'Date');

            // ── Row 3: Amount value / Date value ─────────────────────────
            $sheet->setCellValue("{$startColLetter}3", $feeCollection->isVoluntary ? 'Voluntary' : $feeCollection->amount);
            $nextCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + 1);
            $dateValue = $feeCollection->date?->format('M d,') . "\n" . $feeCollection->date?->format('Y');
            $sheet->setCellValue("{$nextCol}3", $dateValue);
            $sheet->getStyle("{$nextCol}3")->getAlignment()->setWrapText(true);

            // ── Row 4: Paid / Remaining sub-headers ──────────────────────
            $sheet->setCellValue("{$startColLetter}4", 'Paid');
            $remainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + ($subColCount - 1));
            $sheet->setCellValue("{$remainingColLetter}4", 'Remaining');

            // track paid/remaining col letters for SUM formulas
            $this->paidColLetters[] = $startColLetter;
            $this->remainingColLetters[] = $remainingColLetter;

            $col += $subColCount;
        }

        // ── Total Paid column ────────────────────────────────────────────
        if (in_array('total_paid', $this->selectedColumns)) {
            $totalPaidColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->mergeCells("{$totalPaidColLetter}1:{$totalPaidColLetter}3");
            $sheet->setCellValue("{$totalPaidColLetter}1", 'Total Paid');
            $sheet->getStyle("{$totalPaidColLetter}1")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->setCellValue("{$totalPaidColLetter}4", 'Total');
            $col++;
        }

        // ── Total Remaining column ───────────────────────────────────────
        if (in_array('total_remaining', $this->selectedColumns)) {
            $totalRemainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->mergeCells("{$totalRemainingColLetter}1:{$totalRemainingColLetter}3");
            $sheet->setCellValue("{$totalRemainingColLetter}1", 'Total Remaining');
            $sheet->getStyle("{$totalRemainingColLetter}1")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->setCellValue("{$totalRemainingColLetter}4", 'Balance');
            $col++;
        }

        // track last col for buildStyles
        $this->lastCol = $col;
    }

    protected function buildContent($sheet): void
    {
        $index = 1;

        foreach ($this->students as $student) {
            $col = 3;
            $rowNum = $index + 1;

            $sheet->setCellValue('A' . $this->rowIndex, "=" . StudentsSheet::getTitle() . "!A{$rowNum}");
            $sheet->setCellValue('B' . $this->rowIndex, "=" . StudentsSheet::getTitle() . "!B{$rowNum}");

            foreach ($this->feeCollections as $feeCollection) {
                $pivotStudent = $feeCollection->students->firstWhere('id', $student->id);
                $paid = $pivotStudent?->pivot->amount ?? 0;

                $paidColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $remainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $amountColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

                $sheet->setCellValue("{$paidColLetter}{$this->rowIndex}", $paid > 0 ? $paid : '');
                $col++;

                if ($feeCollection->isVoluntary) {
                    $sheet->setCellValue("{$remainingColLetter}{$this->rowIndex}", '');
                } else {
                    $sheet->setCellValue("{$remainingColLetter}{$this->rowIndex}", "=IF({$amountColLetter}\$3-{$paidColLetter}{$this->rowIndex}<=0,\"\",{$amountColLetter}\$3-{$paidColLetter}{$this->rowIndex})");
                }
                $col++;
            }

            // ── Total Paid: =SUM(C5,E5,G5,...) ──────────────────────────
            if (in_array('total_paid', $this->selectedColumns)) {
                $totalPaidColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $paidRefs = implode(',', array_map(
                    fn($letter) => "{$letter}{$this->rowIndex}",
                    $this->paidColLetters
                ));
                $sheet->setCellValue("{$totalPaidColLetter}{$this->rowIndex}", "=SUM({$paidRefs})");
                $col++;
            }

            // ── Total Remaining: =SUM(D5,F5,H5,...) ─────────────────────
            if (in_array('total_remaining', $this->selectedColumns)) {
                $totalRemainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $remainingRefs = implode(',', array_map(
                    fn($letter) => "{$letter}{$this->rowIndex}",
                    $this->remainingColLetters
                ));
                $sheet->setCellValue("{$totalRemainingColLetter}{$this->rowIndex}", "=SUM({$remainingRefs})");
                $col++;
            }

            $this->rowIndex++;
            $index++;
        }
    }

    protected function buildStyles($sheet): void
    {
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->lastCol - 1);
        $lastDataRow = $this->rowIndex - 1;

        // ── Alternating colors per fee collection (full height) ──────────
        $colors = [
            'FFF3F4F6', // gray-100
            'FFFFFFFF', // white
        ];

        $col = 3;
        $colorIndex = 0;

        foreach ($this->feeCollections as $feeCollection) {
            $subColCount = 0;
            $subColCount++;
            $subColCount++;

            $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + $subColCount - 1);

            $sheet->getStyle("{$startColLetter}1:{$endColLetter}{$lastDataRow}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => $colors[$colorIndex % 2]],
                ],
            ]);

            // font color amount and amount value
            $sheet->getStyle("{$startColLetter}2:{$startColLetter}3")->applyFromArray([
                'font' => ['color' => ['argb' => '2563EB']], // blue
            ]);

            // font color date and date value
            $sheet->getStyle("{$endColLetter}2:{$endColLetter}3")->applyFromArray([
                'font' => ['color' => ['argb' => 'FF7C3AED']], // purple
            ]);

            // font color Paid and student cell values
            $sheet->getStyle("{$startColLetter}4:{$startColLetter}{$lastDataRow}")->applyFromArray([
                'font' => ['color' => ['argb' => 'FF16A34A']], // green
            ]);

            // font color Remaining and student cell values
            $sheet->getStyle("{$endColLetter}4:{$endColLetter}{$lastDataRow}")->applyFromArray([
                'font' => ['color' => ['argb' => 'FFDC2626']], // red
            ]);

            $col += $subColCount;
            $colorIndex++;
        }

        // ── Total Paid column styles ─────────────────────────────────────
        if (in_array('total_paid', $this->selectedColumns)) {
            $totalPaidColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$totalPaidColLetter}1:{$totalPaidColLetter}{$lastDataRow}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD1FAE5'], // green-100
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FF16A34A']], // green
            ]);
            $col++;
        }

        // ── Total Remaining column styles ────────────────────────────────
        if (in_array('total_remaining', $this->selectedColumns)) {
            $totalRemainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$totalRemainingColLetter}1:{$totalRemainingColLetter}{$lastDataRow}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFEE2E2'], // red-100
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFDC2626']], // red
            ]);
            $col++;
        }

        // ── Center align C onward ────────────────────────────────────────
        $sheet->getStyle("C:{$lastColLetter}")->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // ── Bold + center align rows 1-4 ─────────────────────────────────
        $sheet->getStyle("A1:{$lastColLetter}4")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ── Auto width ───────────────────────────────────────────────────
        for ($i = 1; $i < $this->lastCol; $i++) {
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i)
            )->setAutoSize(true);
        }

        // ── Number format #,##0 (1,250 / 10,500) ────────────────────────
        $numberFormat = '#,##0';
        $col = 3;

        foreach ($this->feeCollections as $feeCollection) {
            $paidColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $remainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);

            // amount value row 3
            $sheet->getStyle("{$paidColLetter}3")->getNumberFormat()->setFormatCode($numberFormat);

            // paid and remaining data rows
            $sheet->getStyle("{$paidColLetter}5:{$paidColLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle("{$remainingColLetter}5:{$remainingColLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);

            $col += 2;
        }

        if (in_array('total_paid', $this->selectedColumns)) {
            $totalPaidColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$totalPaidColLetter}5:{$totalPaidColLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
            $col++;
        }

        if (in_array('total_remaining', $this->selectedColumns)) {
            $totalRemainingColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$totalRemainingColLetter}5:{$totalRemainingColLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
            $col++;
        }
    }
}
