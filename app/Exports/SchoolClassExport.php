<?php

namespace App\Exports;

use App\Models\SchoolClass;
use App\Exports\Sheets\StudentSheet;
use App\Exports\Sheets\AttendanceSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SchoolClassExport implements WithMultipleSheets
{
    public function __construct(
        protected SchoolClass $schoolClass,
        protected array $data,
    ) {}

    public function sheets(): array
    {
        return [
            new StudentSheet($this->schoolClass, $this->data),
            new AttendanceSheet($this->schoolClass, $this->data),
        ];
    }
}
