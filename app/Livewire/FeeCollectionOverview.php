<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\FeeCollection;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Columns\TextColumn;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class FeeCollectionOverview extends Component implements HasForms, HasTable, HasActions
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
        $query = Student::query()
            ->whereIn('id', SchoolClassResource::getStudents($this->schoolClassId))
            ->withSum([
                'feeCollections as total_paid' => fn($q) => $q->where('school_class_id', $this->schoolClassId),
            ], 'fee_collection_student.amount');

        return $table
            ->query($query)
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...SchoolClassStudentColumns::schema(),

                TextColumn::make('total_paid')
                    ->color('success')
                    ->money('PHP')
                    ->alignCenter()
                    ->underline()
                    ->placeholder('—')
                    ->state(fn($record) => $record->total_paid > 0 ? $record->total_paid : null)
                    ->searchable(false)
                    ->sortable()
                    ->summarize(
                        Summarizer::make()
                            ->label('Overall Total')
                            ->money('PHP')
                            ->using(function ($query) {
                                return FeeCollection::query()
                                    ->where('school_class_id', $this->schoolClassId)
                                    ->join('fee_collection_student', 'fee_collections.id', '=', 'fee_collection_student.fee_collection_id')
                                    ->whereIn('fee_collection_student.student_id', $query->pluck('id'))
                                    ->sum('fee_collection_student.amount');
                            })
                    )
                    ->action(static::getStudentFeePaidAndBalance()),

                TextColumn::make('remaining')
                    ->color('danger')
                    ->money('PHP')
                    ->alignCenter()
                    ->underline()
                    ->placeholder('—')
                    ->getStateUsing(function ($record) {
                        $fixedFees = $record->feeCollections
                            ->where('school_class_id', $this->schoolClassId)
                            ->where('amount', '>', 0);

                        $totalDue = $fixedFees->sum('amount');
                        $totalPaid = $fixedFees->sum('pivot.amount');

                        $remaining = $totalDue - $totalPaid;

                        return $remaining > 0 ? $remaining : null;
                    })
                    ->searchable(false)
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
                    ->summarize(
                        Summarizer::make()
                            ->label('Overall Remaining')
                            ->money('PHP')
                            ->using(function ($query) {
                                $studentIds = $query->pluck('id');

                                $totalDue = FeeCollection::query()
                                    ->where('school_class_id', $this->schoolClassId)
                                    ->where('fee_collections.amount', '>', 0)
                                    ->join('fee_collection_student', 'fee_collections.id', '=', 'fee_collection_student.fee_collection_id')
                                    ->whereIn('fee_collection_student.student_id', $studentIds)
                                    ->sum('fee_collections.amount');

                                $totalPaid = FeeCollection::query()
                                    ->where('school_class_id', $this->schoolClassId)
                                    ->where('fee_collections.amount', '>', 0)
                                    ->join('fee_collection_student', 'fee_collections.id', '=', 'fee_collection_student.fee_collection_id')
                                    ->whereIn('fee_collection_student.student_id', $studentIds)
                                    ->sum('fee_collection_student.amount');

                                $remaining = $totalDue - $totalPaid;

                                return $remaining > 0 ? $remaining : 0;
                            })
                    )
                    ->action(static::getStudentFeePaidAndBalance()),
            ])
            ->filters([
                StudentFilters::gender()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }

    public static function getStudentFeePaidAndBalance()
    {
        return Action::make('studentFeePaidAndBalance')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading(fn($record) => $record->full_name . ' - Fee Collections')
            ->modalContent(fn($record, $livewire) => new HtmlString(
                Blade::render(
                    '@livewire("student-fee-collections", ["studentId" => $studentId, "schoolClassId" => $schoolClassId])',
                    [
                        'studentId' => $record->id,
                        'schoolClassId' => $livewire->schoolClassId,
                    ]
                )
            ));
    }
}
