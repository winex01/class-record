<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\FeeCollection;
use Filament\Support\Enums\Width;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use App\Livewire\Traits\RenderTableTrait;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassFeeCollections;

class StudentFeeCollections extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

    public $studentId;
    public $schoolClassId;
    public $isPaidOrRemaining;

    public function mount($studentId, $schoolClassId, $isPaidOrRemaining)
    {
        $this->studentId = $studentId;
        $this->schoolClassId = $schoolClassId;
        $this->isPaidOrRemaining = $isPaidOrRemaining;

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
            ->columns([
                ...$this->getColumns(),
            ])
            ->filters([
                //
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No fee collection records found.');
    }

    protected function getCOlumns()
    {
        $columns = ManageSchoolClassFeeCollections::getColumns();
        unset($columns['total']);
        unset($columns['status']);

        return [
            ...$columns,

            TextColumn::make('student_amount')
                ->label(fn () => $this->isPaidOrRemaining ? 'Amount Paid' : 'Remaining Balance')
                ->money('PHP')
                ->alignCenter()
                ->getStateUsing(function (FeeCollection $record) {
                    $paidAmount = $record->students->first()?->pivot?->amount ?? 0;

                    if ($this->isPaidOrRemaining) {
                        $amount = $paidAmount;
                    } else {
                        $amount = $record->amount - $paidAmount;
                    }

                    return $amount > 0 ? $amount : null;
                })
                ->placeholder('—')
                ->color($this->isPaidOrRemaining ? 'success' : 'danger')
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    if ($this->isPaidOrRemaining) {
                        return $query->orderByRaw(
                            'CAST(COALESCE((
                                SELECT SUM(amount) FROM fee_collection_student
                                WHERE fee_collection_student.fee_collection_id = fee_collections.id
                                AND fee_collection_student.student_id = ?
                            ), -1) AS DECIMAL(10,2)) ' . $direction,
                            [$this->studentId]
                        );
                    } else {
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
                    }
                })
                ->summarize(
                Summarizer::make()
                    ->label('Total')
                    ->money('PHP')
                    ->using(function ($query) {
                        return FeeCollection::query()
                            ->whereIn('id', $query->pluck('id'))
                            ->with(['students' => fn ($q) => $q->where('students.id', $this->studentId)])
                            ->get()
                            ->sum(function ($record) {
                                $paidAmount = $record->students->first()?->pivot?->amount ?? 0;

                                if ($this->isPaidOrRemaining) {
                                    $amount = $paidAmount;
                                } else {
                                    $amount = $record->amount - $paidAmount;
                                }

                                return $amount > 0 ? $amount : 0;
                            });
                    })
            )
            ->action($this->updateAmountAction()),
        ];
    }

    protected function updateAmountAction()
    {
        return Action::make('updateAmountPaid')
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
                ->action(function ($record, array $data) {
                    // Update only the score
                    $record->students()->updateExistingPivot($this->studentId, [
                        'amount' => $data['amount'],
                    ]);

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
