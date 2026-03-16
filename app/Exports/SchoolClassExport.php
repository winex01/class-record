<?php

namespace App\Exports;

use App\Models\SchoolClass;
use App\Exports\Sheets\StudentsSheet;
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
            new StudentsSheet($this->schoolClass, $this->data),
        ];
    }
}
