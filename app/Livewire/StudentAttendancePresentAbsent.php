<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class StudentAttendancePresentAbsent extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

    public Attendance $attendance;
    public bool $isPresent;

    public function mount(Attendance $attendance, bool $isPresent)
    {
        $this->attendance = $attendance;
        $this->isPresent = $isPresent;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $studentIds = $this->attendance
            ->students()
            ->where('present', $this->isPresent)
            ->pluck('students.id');

        return $table
            ->query(Student::query()->whereIn('id', $studentIds))
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),
            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false);
    }
}
