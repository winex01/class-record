<?php

namespace App\Livewire;

use App\Services\Icon;
use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
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
                        $totalPaid = $record->feeCollections->sum('pivot.amount');

                        return $totalPaid > 0 ? $totalPaid : null;
                    })
                    ->placeholder('—')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum([
                            'feeCollections as total_paid_sort' => function ($q) {
                                $q->where('school_class_id', $this->schoolClassId);
                            }
                        ], 'fee_collection_student.amount')
                        ->orderBy('total_paid_sort', $direction);
                    })
                    ->action(
                        Action::make('totalPaidFeeCollections')
                                    ->modalHeading(fn ($record) => $record->full_name . ' - Fee Collections')
                                    ->icon(Icon::assessments())
                                    ->modalContent(function ($record, $livewire) {
                                        return new HtmlString(
                                            Blade::render(
                                                <<<'BLADE'
                                                <div>
                                                    @livewire('student-fee-collections', [
                                                        'studentId' => $studentId,
                                                        'schoolClassId' => $schoolClassId,
                                                        'isPaidOrRemaining' => $isPaidOrRemaining,
                                                    ])
                                                </div>
                                                BLADE,
                                                [
                                                    'studentId' => $record->id,
                                                    'schoolClassId' => $livewire->schoolClassId,
                                                    'isPaidOrRemaining' => true,
                                                ]
                                            )
                                        );
                                    })
                                    ->modalSubmitAction(false)
                                    ->modalCancelAction(false)
                                    ->modalWidth(Width::TwoExtraLarge)
                    ),

                TextColumn::make('remaining')
                    ->color('danger')
                    ->money('PHP')
                    ->alignCenter()
                    ->getStateUsing(function (Student $record) {
                        $fixedFees = $record->feeCollections->where('amount', '>', 0);

                        $totalDue = $fixedFees->sum('amount');
                        $totalPaid = $fixedFees->sum('pivot.amount');

                        $remaining = $totalDue - $totalPaid;

                        return $remaining > 0 ? $remaining : null;
                    })
                    ->placeholder('—')
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
                    })
                    ->action(
                        Action::make('remainingFeeCollections')
                            ->icon(Icon::assessments())
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalWidth(Width::TwoExtraLarge)
                            ->modalHeading(fn ($record) => $record->full_name . ' - Fee Collections')
                            ->modalContent(fn ($record, $livewire) => new HtmlString(
                                Blade::render(
                                    '@livewire("student-fee-collections", ["studentId" => $studentId, "schoolClassId" => $schoolClassId, "isPaidOrRemaining" => $isPaidOrRemaining])',
                                    [
                                        'studentId' => $record->id,
                                        'schoolClassId' => $livewire->schoolClassId,
                                        'isPaidOrRemaining' => false,
                                    ]
                                )
                            ))
                    ),

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
}
