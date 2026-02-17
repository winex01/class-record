<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use App\Models\FeeCollection;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassFeeCollections;

class StudentFeeCollections extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

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

                        return $amount > 0 ? $amount : null; // Return null if zero
                    })
                    ->placeholder('—') // Optional: show dash for null values
                    ->color($this->isPaidOrRemaining ? 'success' : 'danger')
        ];
    }

    public function render()
    {
        return view('livewire.student-fee-collections');
    }
}
