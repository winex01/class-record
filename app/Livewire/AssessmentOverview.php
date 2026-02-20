<?php

namespace App\Livewire;

use App\Services\Icon;
use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class AssessmentOverview extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

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

        $this->studentsData = static::processData($assessments);
    }

    private static function processData($assessments): array
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
            ->emptyStateDescription(false)
            ->recordActions([
                Action::make('assessmentLists')
                    ->label('Assessments')
                    ->modalHeading(fn ($record) => $record->full_name . ' - Assessments')
                    ->icon(Icon::assessments())
                    ->modalContent(function ($record, $livewire) {
                        return new HtmlString(
                            Blade::render(
                                <<<'BLADE'
                                <div>
                                    @livewire('student-assessment-lists', [
                                        'studentId' => $studentId,
                                        'schoolClassId' => $schoolClassId,
                                    ])
                                </div>
                                BLADE,
                                [
                                    'studentId' => $record->id,
                                    'schoolClassId' => $livewire->schoolClassId,
                                ]
                            )
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth(Width::TwoExtraLarge)
            ]);
    }
}
