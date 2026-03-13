<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Columns\TextColumn;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class AttendanceOverview extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

    public $schoolClassId;

    public function mount($schoolClassId)
    {
        $this->schoolClassId = $schoolClassId;
        $this->resetTable();

        // Set default active tab to the first tab key
        $tabs = $this->getTabs();
        $this->activeTab = array_key_first($tabs);
    }

    public function getTabs(): array
    {
        $schoolClassStudents = SchoolClassResource::getStudents($this->schoolClassId);

        // Get the count of students with perfect attendance
        $perfectAttendanceCount = Student::query()
            ->whereIn('id', $schoolClassStudents)
            ->whereDoesntHave('attendances', function ($q) {
                $q->where('present', false); // No absent records
            })
            ->whereHas('attendances') // Has at least one attendance record
            ->count();

        return [
            'all' => Tab::make('All')
                ->badge(count($schoolClassStudents)),
            'perfect_attendance' => Tab::make('Perfect Attendance')
                ->badge($perfectAttendanceCount)
                ->badgeColor('success'), // Optional: set badge color
        ];
    }

    public function table(Table $table): Table
    {
        $query = Student::query()
            ->whereIn('id', SchoolClassResource::getStudents($this->schoolClassId));

        // Apply tab filtering based on activeTab
        if ($this->activeTab === 'perfect_attendance') {
            // Students with ALL attendances marked as present (no absences)
            $query->whereDoesntHave('attendances', function ($q) {
                $q->where('present', false); // No absent records
            })->whereHas('attendances'); // But has at least one attendance record
        }

        return $table
            ->query($query)
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...SchoolClassStudentColumns::schema(),

                TextColumn::make('present')
                    ->searchable(false)
                    ->label('Present')
                    ->color('success')
                    ->alignCenter()
                    ->underline()
                    ->state(fn ($record) => $record->attendances()->wherePivot('present', true)->count())
                    ->sortable(query: function ($query, string $direction) {
                        return $query
                            ->withCount([
                                'attendances as present_count' => function ($query) {
                                    $query->where('attendance_student.present', true);
                                }
                            ])
                            ->orderBy('present_count', $direction);
                    })
                    ->action(
                        Action::make('viewPresences')
                            ->modalHeading(fn ($record) => $record->full_name . ' - Present Dates')
                            ->modalContent(self::studentAttendanceDatesModal(true))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalWidth(Width::Medium)
                    ),

                TextColumn::make('absent')
                    ->searchable(false)
                    ->label('Absent')
                    ->color('danger')
                    ->alignCenter()
                    ->underline()
                    ->state(fn ($record) => $record->attendances()->wherePivot('present', false)->count())
                    ->sortable(query: function ($query, string $direction) {
                        return $query
                            ->withCount([
                                'attendances as absent_count' => function ($query) {
                                    $query->where('attendance_student.present', false);
                                }
                            ])
                            ->orderBy('absent_count', $direction);
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
            ->emptyStateDescription(false)
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
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
}
