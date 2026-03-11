<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use App\Models\FeeCollection;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use App\Filament\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use App\Livewire\Traits\RenderTableTrait;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use App\Filament\Traits\ManageActionVisibility;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassFeeCollectionColumns;

class StudentFeeCollections extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;
    use ManageActionVisibility;

    public $studentId;
    public $schoolClassId;
    public $isReadOnly = false;

    public function mount($studentId, $schoolClassId)
    {
        $this->studentId = $studentId;
        $this->schoolClassId = $schoolClassId;
        $this->isReadOnly = !SchoolClass::findOrFail($this->schoolClassId)->active;

        // Reset table page to 1 on mount or everytime modal is open
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->query(
            FeeCollection::query()
                    ->where('school_class_id', $this->schoolClassId)
                    ->whereHas('students', function ($query) {
                        $query->where('student_id', $this->studentId);
                    })
                    ->with(['students' => function ($query) {
                        $query->where('student_id', $this->studentId);
                    }])
            )
            ->columns($this->getColumns())
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No fee collection records found.')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }

    protected function getCOlumns()
    {
        $columns = SchoolClassFeeCollectionColumns::schema();
        unset($columns['total']);
        unset($columns['status']);

        return [
            ...$columns,

            TextColumn::make('paid')
                ->money('PHP')
                ->color('success')
                ->alignCenter()
                ->underline(!$this->isReadOnly)
                ->placeholder('—')
                ->getStateUsing(function (FeeCollection $record) {
                    $paidAmount = $record->students->first()?->pivot?->amount ?? 0;
                    return $paidAmount > 0 ? $paidAmount : null;
                })
                ->searchable(false)
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->orderByRaw(
                        'CAST(COALESCE((
                            SELECT SUM(amount) FROM fee_collection_student
                            WHERE fee_collection_student.fee_collection_id = fee_collections.id
                            AND fee_collection_student.student_id = ?
                        ), -1) AS DECIMAL(10,2)) ' . $direction,
                        [$this->studentId]
                    );
                })
                ->summarize(
                    Summarizer::make()
                        ->label('Total Paid')
                        ->money('PHP')
                        ->using(function ($query) {
                            return FeeCollection::query()
                                ->whereIn('id', $query->pluck('id'))
                                ->with(['students' => fn ($q) => $q->where('students.id', $this->studentId)])
                                ->get()
                                ->sum(function ($record) {
                                    $paidAmount = $record->students->first()?->pivot?->amount ?? 0;
                                    return $paidAmount > 0 ? $paidAmount : 0;
                                });
                        })
                )
                ->action($this->isReadOnly ? null : $this->updateAmountAction()),

            TextColumn::make('remaining')
                ->money('PHP')
                ->color('danger')
                ->alignCenter()
                ->underline(!$this->isReadOnly)
                ->placeholder('—')
                ->getStateUsing(function (FeeCollection $record) {
                    $paidAmount = $record->students->first()?->pivot?->amount ?? 0;
                    $remaining = $record->amount - $paidAmount;
                    return $remaining > 0 ? $remaining : null;
                })
                ->searchable(false)
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->orderByRaw(
                        'CASE
                            WHEN (fee_collections.amount - COALESCE((
                                SELECT SUM(amount) FROM fee_collection_student
                                WHERE fee_collection_student.fee_collection_id = fee_collections.id
                                AND fee_collection_student.student_id = ?
                            ), 0)) <= 0 THEN -1
                            ELSE (fee_collections.amount - COALESCE((
                                SELECT SUM(amount) FROM fee_collection_student
                                WHERE fee_collection_student.fee_collection_id = fee_collections.id
                                AND fee_collection_student.student_id = ?
                            ), 0))
                        END ' . $direction,
                        [$this->studentId, $this->studentId]
                    );
                })
                ->summarize(
                    Summarizer::make()
                        ->label('Remaining Balance')
                        ->money('PHP')
                        ->using(function ($query) {
                            return FeeCollection::query()
                                ->whereIn('id', $query->pluck('id'))
                                ->with(['students' => fn ($q) => $q->where('students.id', $this->studentId)])
                                ->get()
                                ->sum(function ($record) {
                                    $paidAmount = $record->students->first()?->pivot?->amount ?? 0;
                                    $remaining = $record->amount - $paidAmount;
                                    return $remaining > 0 ? $remaining : 0;
                                });
                        })
                )
                ->action($this->isReadOnly ? null : $this->updateAmountAction()),
        ];
    }

    protected function updateAmountAction()
    {
        return Action::make('updateAmountPaid')
                ->disabled($this->isReadOnly)
                ->form([
                    Section::make()
                        ->columns(3)
                        ->schema([
                            Placeholder::make('fee_amount')
                                ->label('Amount')
                                ->columnSpan(1)
                                ->color('info')
                                ->content(fn ($record) =>
                                    $record->amount == 0
                                        ? '—'
                                        : '₱' . number_format($record->amount, 2)
                                ),

                            Placeholder::make('amount_paid')
                                ->label('Paid')
                                ->columnSpan(1)
                                ->color('success')
                                ->content(function ($record, $get) {
                                    $paid = $get('amount') ?? 0;

                                    return '₱' . number_format($paid, 2);
                                }),

                            Placeholder::make('balance')
                                ->label('Balance')
                                ->columnSpan(1)
                                ->color('danger')
                                ->content(function ($record, $get) {
                                    if ($record->amount == 0) {
                                        return '—';
                                    }

                                    $paid = $get('amount') ?? 0;
                                    $balance = $record->amount - $paid;

                                    return '₱' . number_format($balance, 2);
                                }),
                        ]),

                    Section::make()
                        ->schema([
                            TextInput::make('amount')
                                ->numeric()
                                ->required()
                                ->live()
                                ->default(function ($record) {
                                    return $record->students()
                                        ->where('student_id', $this->studentId)
                                        ->first()
                                        ?->pivot
                                        ?->amount;
                                })
                                ->placeholder(fn ($record) =>
                                    $record->amount == 0
                                        ? '₱'
                                        : 'Fee ₱' . ($record->amount ?? 0)
                                )
                                ->rules(function ($record) {
                                    $amount = $record->amount ?? 0;

                                    return $amount > 0
                                        ? ['numeric', 'min:0', 'max:' . $amount]
                                        : ['numeric', 'min:0'];
                                }),
                        ]),
                ])
                ->action(function ($livewire, $record, array $data) {
                    // Update only the score
                    $record->students()->updateExistingPivot($this->studentId, [
                        'amount' => $data['amount'],
                    ]);

                    $livewire->dispatch('refreshCollapsibleTableWidget');

                    Notification::make()
                        ->title('Amount updated successfully')
                        ->success()
                        ->send();
                })
                ->modalHeading('Update Fee Amount')
                ->modalSubmitActionLabel('Save')
                ->modalWidth(Width::Medium);
    }
}
