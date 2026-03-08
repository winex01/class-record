<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Columns\TextColumn;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class AttendanceOverview extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $schoolClassId;
    public $studentsData = [];
    public $perfectAttendanceData = [];
    public $activeTab = 'all';

    public function mount($schoolClassId)
    {
        $this->schoolClassId = $schoolClassId;
        $this->loadData();
        $this->resetTable();
    }

    public function loadData()
    {
        $attendances = SchoolClass::find($this->schoolClassId)
            ->attendances()
            ->with('students')
            ->get();

        $this->studentsData = static::processData($attendances);

        // Filter for perfect attendance
        $this->perfectAttendanceData = array_filter($this->studentsData, function($student) {
            return $student['absent_count'] === 0;
        });
        $this->perfectAttendanceData = array_values($this->perfectAttendanceData);
    }

    private static function processData($attendances): array
    {
        $studentsData = [];

        foreach ($attendances as $attendance) {
            foreach ($attendance->students as $student) {
                if (!isset($studentsData[$student->id])) {
                    $studentsData[$student->id] = [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'present_count' => 0,
                        'absent_count' => 0,
                    ];
                }

                // Count based on the 'present' boolean pivot column
                if ($student->pivot->present) {
                    $studentsData[$student->id]['present_count']++;
                } else {
                    $studentsData[$student->id]['absent_count']++;
                }
            }
        }

        return array_values($studentsData);
    }

    public function updatedActiveTab()
    {
        $this->resetTable();
    }

    #[On('refresh-attendance-overview-data')]
    public function refreshOverviewData()
    {
        $this->loadData();
    }

    public function getCurrentStudentsData()
    {
        return $this->activeTab === 'all' ? $this->studentsData : $this->perfectAttendanceData;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->whereIn('id', array_column($this->getCurrentStudentsData(), 'id')))
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...SchoolClassStudentColumns::schema(),

                TextColumn::make('present_count')
                    ->label('Present')
                    ->color('success')
                    ->alignCenter()
                    ->underline()
                    ->state(function ($record) {
                        $studentData = collect($this->getCurrentStudentsData())->firstWhere('id', $record->id);
                        return $studentData['present_count'] ?? 0;
                    })
                    ->searchable(false)
                    ->sortable(query: function ($query, $direction) {
                        $studentsData = $this->getCurrentStudentsData();
                        $orderMap = collect($studentsData)->sortBy('present_count', SORT_REGULAR, $direction === 'desc')->pluck('id')->toArray();

                        return $query->orderByRaw('FIELD(id, ' . implode(',', $orderMap) . ')');
                    })
                    ->action(
                        Action::make('viewPresences')
                            ->modalHeading(fn ($record) => $record->full_name . ' - Present Dates')
                            ->modalContent(self::studentAttendanceDatesModal(true))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalWidth(Width::Small)
                    ),

                TextColumn::make('absent_count')
                    ->label('Absent')
                    ->color('danger')
                    ->alignCenter()
                    ->underline()
                    ->state(function ($record) {
                        $studentData = collect($this->getCurrentStudentsData())->firstWhere('id', $record->id);
                        return $studentData['absent_count'] ?? 0;
                    })
                    ->searchable(false)
                    ->sortable(query: function ($query, $direction) {
                        $studentsData = $this->getCurrentStudentsData();
                        $orderMap = collect($studentsData)->sortBy('absent_count', SORT_REGULAR, $direction === 'desc')->pluck('id')->toArray();

                        return $query->orderByRaw('FIELD(id, ' . implode(',', $orderMap) . ')');
                    })
                    ->action(
                        Action::make('viewAbsences')
                            ->modalHeading(fn ($record) => $record->full_name . ' - Absent Dates')
                            ->modalContent(self::studentAttendanceDatesModal(false))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalWidth(Width::Medium)
                    ),
            ])
            ->filters([
                StudentFilters::gender()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false);
    }

    private static function studentAttendanceDatesModal(bool $isPresent)
    {
        return fn ($record, $livewire) => new HtmlString(
            Blade::render(
                '@livewire("student-attendance-dates", ["studentId" => $studentId, "schoolClassId" => $schoolClassId, "isPresent" => $isPresent])',
                [
                    'studentId' => $record->id,
                    'schoolClassId' => $livewire->schoolClassId,
                    'isPresent' => $isPresent,
                ]
            )
        );
    }

    public function render()
    {
        return view('livewire.attendance-overview');
    }
}
