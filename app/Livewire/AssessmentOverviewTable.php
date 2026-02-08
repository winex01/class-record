<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Livewire\Attributes\On;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class AssessmentOverviewTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $schoolClassId;
    public $studentsData = [];

    public function mount($schoolClassId)
    {
        $this->schoolClassId = $schoolClassId;
        $this->loadData();
        $this->resetTable();
    }

    public function loadData()
    {
        $assessments = SchoolClass::find($this->schoolClassId)
            ->assessments()
            ->with('students')
            ->get();

        $this->studentsData = static::calculateStudentsAssessmentData($assessments);
    }

    #[On('refresh-assessment-overview-data')]
    public function refreshOverviewData()
    {
        $this->loadData();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->whereIn('id', array_column($this->studentsData, 'id')))
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

    public function render()
    {
        return view('livewire.assessment-overview-table');
    }

    private static function calculateStudentsAssessmentData($assessments): array
    {
        $studentsData = [];

        foreach ($assessments as $assessment) {
            foreach ($assessment->students as $student) {
                if (!isset($studentsData[$student->id])) {
                    $studentsData[$student->id] = [
                        'id' => $student->id,
                        'name' => $student->full_name,
                    ];
                }
            }
        }

        return array_values($studentsData);
    }
}
