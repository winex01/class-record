<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
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
    public $highPerformersData = [];
    public $lowPerformersData = [];
    public $activeTab = 'all';

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

        // Filter for high performers (average >= 85)
        $this->highPerformersData = array_filter($this->studentsData, function($student) {
            return $student['average_score'] >= 85;
        });
        $this->highPerformersData = array_values($this->highPerformersData);

        // Filter for low performers (average < 70)
        $this->lowPerformersData = array_filter($this->studentsData, function($student) {
            return $student['average_score'] < 70 && $student['average_score'] > 0;
        });
        $this->lowPerformersData = array_values($this->lowPerformersData);
    }

    public function updatedActiveTab()
    {
        $this->resetTable();
    }

    #[On('refresh-assessment-overview-data')]
    public function refreshOverviewData()
    {
        $this->loadData();
    }

    public function getCurrentStudentsData()
    {
        return match($this->activeTab) {
            'high_performers' => $this->highPerformersData,
            'low_performers' => $this->lowPerformersData,
            default => $this->studentsData,
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->whereIn('id', array_column($this->getCurrentStudentsData(), 'id')))
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                TextColumn::make('assessments')
                    ->label('Assessments')
                    ->state(function ($record) {
                        $studentData = collect($this->getCurrentStudentsData())->firstWhere('id', $record->id);
                        return $studentData['total_assessments'] ?? 0;
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->sortable(query: function ($query, $direction) {
                        $studentsData = $this->getCurrentStudentsData();
                        $orderMap = collect($studentsData)->sortBy('total_assessments', SORT_REGULAR, $direction === 'desc')->pluck('id')->toArray();

                        return $query->orderByRaw('FIELD(id, ' . implode(',', $orderMap) . ')');
                    })
                    ->action(
                        Action::make('viewAssessments')
                            ->modalHeading(fn ($record) => $record->full_name . ' - Assessments')
                            ->modalContent(fn ($record, $livewire) => view('filament.components.student-assessment-details', [
                                'studentId' => $record->id,
                                'schoolClassId' => $livewire->schoolClassId,
                            ]))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalWidth(Width::Small)
                    ),

                TextColumn::make('average_score')
                    ->label('Average Score')
                    ->state(function ($record) {
                        $studentData = collect($this->getCurrentStudentsData())->firstWhere('id', $record->id);
                        return $studentData['average_score'] ?? '-';
                    })
                    ->alignCenter()
                    ->badge()
                    ->color(function ($record) {
                        $studentData = collect($this->getCurrentStudentsData())->firstWhere('id', $record->id);
                        $avg = $studentData['average_score'] ?? 0;

                        if ($avg >= 85) return 'success';
                        if ($avg >= 70) return 'warning';
                        return 'danger';
                    })
                    ->sortable(query: function ($query, $direction) {
                        $studentsData = $this->getCurrentStudentsData();
                        $orderMap = collect($studentsData)->sortBy('average_score', SORT_REGULAR, $direction === 'desc')->pluck('id')->toArray();

                        return $query->orderByRaw('FIELD(id, ' . implode(',', $orderMap) . ')');
                    }),
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
                        'total_assessments' => 0,
                        'total_score' => 0,
                        'average_score' => 0,
                    ];
                }

                $score = $student->pivot->score;

                $studentsData[$student->id]['total_assessments']++;
                $studentsData[$student->id]['total_score'] += $score;
            }
        }

        // Calculate averages
        foreach ($studentsData as &$studentData) {
            if ($studentData['total_assessments'] > 0) {
                $studentData['average_score'] = round($studentData['total_score'] / $studentData['total_assessments'], 2);
            }
        }

        return array_values($studentsData);
    }
}
