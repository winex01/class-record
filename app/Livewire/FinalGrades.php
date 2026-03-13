<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Filament\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use App\Services\GradeComputationService;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class FinalGrades extends Component implements HasForms, HasTable, HasActions
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
    }

    public function table(Table $table): Table
    {
        $query = Student::query()->whereIn('id', SchoolClassResource::getStudents($this->schoolClassId));

        return $table
            ->query($query)
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...SchoolClassStudentColumns::schema(),
                ...$this->columnsSchema(),
            ])
            ->filters([
                StudentFilters::gender()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }

    public function columnsSchema()
    {
        $grades = Grade::where('school_class_id', $this->schoolClassId)->get();

        // Cache services outside the closure — one per grade, not one per student
        $gradeServices = $grades->mapWithKeys(fn(Grade $grade) => [
            $grade->grading_period => new GradeComputationService($grade)
        ]);

        $columns = [];

        foreach ($grades as $grade) {
            $snakeCase = $grade->grading_period;

            $columns[$snakeCase] = TextColumn::make($snakeCase)
                ->label($grade->grading_period)
                ->alignCenter()
                ->width('1%')
                ->color('primary')
                ->state(function ($record) use ($gradeServices, $snakeCase) {
                    return $gradeServices->get($snakeCase)->gradingPeriodGrade($record->id);
                })
                ->searchable(false)
                ->sortable(query: function ($query, $direction) use ($gradeServices, $snakeCase) {
                    $service = $gradeServices->get($snakeCase);

                    $sorted = $query->get()
                        ->sortBy(
                            fn($record) => $service->gradingPeriodGrade($record->id),
                            SORT_REGULAR,
                            $direction === 'desc'
                        )
                        ->pluck('id')
                        ->values();

                    $case = $sorted->map(fn($id, $index) => "WHEN {$id} THEN {$index}")->implode(' ');

                    return $query->orderByRaw("CASE id {$case} END");
                });

        }

        // Final grade column — average of all grading period grades
        $columns['final_grade'] = TextColumn::make('final_grade')
            ->label('Final Grade')
            ->alignCenter()
            ->width('1%')
            ->color('info')
            ->state(function ($record) use ($gradeServices) {
                $avg = $gradeServices->avg(function ($service) use ($record) {
                    return $service->gradingPeriodGrade($record->id);
                });

                return round($avg);
            })
            ->searchable(false)
            ->sortable(query: function ($query, $direction) use ($gradeServices) {
                $sorted = $query->get()
                    ->sortBy(
                        fn($record) => $gradeServices->avg(
                            fn($service) => $service->gradingPeriodGrade($record->id)
                        ),
                        SORT_REGULAR,
                        $direction === 'desc'
                    )
                    ->pluck('id')
                    ->values();

                $case = $sorted->map(fn($id, $index) => "WHEN {$id} THEN {$index}")->implode(' ');

                return $query->orderByRaw("CASE id {$case} END");
            });

        return $columns;
    }
}
