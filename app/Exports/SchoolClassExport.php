<?php

namespace App\Exports;

use App\Models\SchoolClass;
use App\Exports\Sheets\GradesSheet;
use App\Exports\Sheets\LessonsSheet;
use App\Exports\Sheets\StudentsSheet;
use App\Exports\Sheets\AttendanceSheet;
use App\Exports\Sheets\FeeCollectionsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SchoolClassExport implements WithMultipleSheets
{
    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        // Students sheet is always included (toggle is locked on)
        $sheets[] = new StudentsSheet($this->schoolClass, $this->data);

        if ($this->data['attendance_enabled'] ?? true) {
            $sheets[] = new AttendanceSheet($this->schoolClass, $this->data);
        }

        if ($this->data['lesson_enabled'] ?? true) {
            $sheets[] = new LessonsSheet($this->schoolClass, $this->data);
        }

        if ($this->data['fee_collection_enabled'] ?? true) {
            $sheets[] = new FeeCollectionsSheet($this->schoolClass, $this->data);
        }

        if ($this->data['grade_enabled'] ?? true) {
            $gradeSheets = $this->schoolClass->grades()
                ->get()
                ->map(fn($grade) => new GradesSheet($this->schoolClass, $this->data, $grade))
                ->toArray();

            array_push($sheets, ...$gradeSheets);
        }

        return $sheets;
    }
}
