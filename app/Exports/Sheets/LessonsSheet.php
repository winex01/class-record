<?php

namespace App\Exports\Sheets;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class LessonsSheet implements WithTitle, WithEvents
{
    protected $lessons;
    protected $selectedColumns;
    protected int $rowIndex = 2; // row 1 is the header

    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {
        $this->lessons = $schoolClass->lessons()->get();

        $columnOrder = ['title', 'description', 'tags', 'completion_date', 'status', 'checklists'];
        $selected = $this->data['lesson_columns'] ?? [];
        $this->selectedColumns = array_values(
            array_filter($columnOrder, fn($col) => in_array($col, $selected))
        );
    }

    public function title(): string
    {
        return 'Lessons';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getTabColor()->setARGB('FF7C3AED');
                $sheet = $event->sheet->getDelegate();

                $this->buildHeaders($sheet);
                $this->buildContent($sheet);
                $this->buildStyles($sheet);
            },
        ];
    }

    protected function buildHeaders($sheet): void
    {
        $col = 1;

        // # column always first
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '1', '#');

        if (in_array('title', $this->selectedColumns)) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '1', 'Title');
        }
        if (in_array('description', $this->selectedColumns)) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '1', 'Description');
        }
        if (in_array('tags', $this->selectedColumns)) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '1', 'Tags');
        }
        if (in_array('completion_date', $this->selectedColumns)) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '1', 'Completion');
        }
        if (in_array('status', $this->selectedColumns)) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . '1', 'Status');
        }
        // checklists skipped — handled in buildContent
    }

    protected function buildContent($sheet): void
    {
        $index = 1;

        foreach ($this->lessons as $lesson) {
            $col = 2; // start at 2 because col 1 is #

            // # index
            $sheet->setCellValue("A{$this->rowIndex}", $index++);

            if (in_array('title', $this->selectedColumns)) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", $lesson->title);
                $col++;
            }

            if (in_array('description', $this->selectedColumns)) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", $lesson->description);
                $col++;
            }

            if (in_array('tags', $this->selectedColumns)) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", is_array($lesson->tags) ? implode(', ', $lesson->tags) : $lesson->tags);
                $col++;
            }

            if (in_array('completion_date', $this->selectedColumns)) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", $lesson->completion_date?->format('M d, Y'));
                $col++;
            }

            if (in_array('status', $this->selectedColumns)) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", $lesson->status);

                $statusValues = implode(',', array_column(\App\Enums\LessonStatus::cases(), 'value'));

                $validation = $sheet->getCell("{$colLetter}{$this->rowIndex}")->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowDropDown(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setFormula1("\"{$statusValues}\"");

                $col++;
            }

            if (in_array('checklists', $this->selectedColumns)) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $doneColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);

                // checklist header merged across Item and Done columns
                $sheet->mergeCells("{$colLetter}{$this->rowIndex}:{$doneColLetter}{$this->rowIndex}");
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", 'Checklist Items and Status');
                $sheet->getStyle("{$colLetter}{$this->rowIndex}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF7C3AED'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $this->rowIndex++;

                // sub-header Item and Done
                $sheet->setCellValue("{$colLetter}{$this->rowIndex}", 'Item');
                $sheet->setCellValue("{$doneColLetter}{$this->rowIndex}", 'Done');
                $sheet->getStyle("{$colLetter}{$this->rowIndex}:{$doneColLetter}{$this->rowIndex}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF6D28D9'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFede9fe'],
                    ],
                ]);
                $this->rowIndex++;

                if (!empty($lesson->checklists)) {
                    foreach ($lesson->checklists as $checklist) {
                        $sheet->setCellValue("{$colLetter}{$this->rowIndex}", $checklist['item']);
                        $sheet->setCellValue("{$doneColLetter}{$this->rowIndex}", $checklist['done'] ? '✓' : '✗');

                        $validation = $sheet->getCell("{$doneColLetter}{$this->rowIndex}")->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(false);
                        $validation->setShowDropDown(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setFormula1('"✓,✗"');

                        $this->rowIndex++;
                    }
                }

                $col += 2;
            }

            $this->rowIndex++;
        }
    }

    protected function buildStyles($sheet): void
    {
        $col = 2; // start at 2 because col 1 is #
        $descriptionColLetter = null;

        foreach ($this->selectedColumns as $column) {
            if ($column === 'description') {
                $descriptionColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            }
            if ($column === 'checklists') {
                $col += 2;
            } else {
                $col++;
            }
        }
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);

        // row 1 header styles
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF7C3AED'],
            ],
        ]);

        // auto width for all columns except description
        for ($i = 1; $i < $col; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            if ($colLetter === $descriptionColLetter)
                continue;
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // fixed width + wrap text for description column
        if ($descriptionColLetter) {
            $sheet->getColumnDimension($descriptionColLetter)->setAutoSize(false)->setWidth(50);
            $sheet->getStyle("{$descriptionColLetter}2:{$descriptionColLetter}{$this->rowIndex}")
                ->getAlignment()->setWrapText(true);
        }
    }
}
