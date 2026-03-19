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
        $gradeSheets = $this->schoolClass->grades()
            ->get()
            ->map(fn ($grade) => new GradesSheet($this->schoolClass, $this->data, $grade))
            ->toArray();

        return [
            new StudentsSheet($this->schoolClass, $this->data),
            new AttendanceSheet($this->schoolClass, $this->data),
            new LessonsSheet($this->schoolClass, $this->data),
            new FeeCollectionsSheet($this->schoolClass, $this->data),

            ...$gradeSheets,

        ];
    }
}
