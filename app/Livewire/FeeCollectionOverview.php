<?php

namespace App\Livewire;

use App\Services\Icon;
use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class FeeCollectionOverview extends Component implements HasForms, HasTable, HasActions
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
        $feeCollections = SchoolClass::find($this->schoolClassId)
            ->feeCollections()
            ->with('students')
            ->get();

        $this->studentsData = static::processData($feeCollections);
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

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->whereIn('id', array_column($this->studentsData, 'id')))
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                TextColumn::make('total_paid')
                    ->color('success')
                    ->money('PHP')
                    ->alignCenter()
                    ->getStateUsing(function (Student $record) {
                        return $record->feeCollections->sum('pivot.amount');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum([
                            'feeCollections as total_paid_sort' => function ($q) {
                                $q->where('school_class_id', $this->schoolClassId);
                            }
                        ], 'fee_collection_student.amount')
                        ->orderBy('total_paid_sort', $direction);
                    })
                    ->action(
                        Action::make('assessmentLists')
                                    ->label('Assessments')
                                    ->modalHeading(fn ($record) => $record->full_name . ' - Fee Collections')
                                    ->icon(Icon::assessments())
                                    ->modalContent(fn ($record, $livewire) => view('filament.components.student-fee-collections', [
                                        'studentId' => $record->id,
                                        'schoolClassId' => $livewire->schoolClassId,
                                        'isPaidOrRemaining' => true, // True = Paid
                                    ]))
                                    ->modalSubmitAction(false)
                                    ->modalCancelAction(false)
                                    ->modalWidth(Width::TwoExtraLarge)
                    ),

                TextColumn::make('remaining')
                    ->color('danger')
                    ->money('PHP')
                    ->alignCenter()
                    ->getStateUsing(function (Student $record) {
                        $totalDue = $record->feeCollections->sum('amount');
                        $totalPaid = $record->feeCollections->sum('pivot.amount');

                        return $totalDue - $totalPaid;
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum([
                            'feeCollections as total_due_sort' => function ($q) {
                                $q->where('school_class_id', $this->schoolClassId);
                            }
                        ], 'amount')
                        ->withSum([
                            'feeCollections as total_paid_sort' => function ($q) {
                                $q->where('school_class_id', $this->schoolClassId);
                            }
                        ], 'fee_collection_student.amount')
                        ->orderByRaw("(total_due_sort - total_paid_sort) {$direction}");
                    }),
                    // TODO:: action to display the feecolltions

            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->recordActions([
                //
            ]);
    }

    public function render()
    {
        return view('livewire.fee-collection-overview');
    }
}
