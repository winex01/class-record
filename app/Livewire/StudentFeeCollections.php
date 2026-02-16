<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use App\Models\FeeCollection;
use Filament\Forms\Contracts\HasForms;
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
                        $query->where('student_id', $this->studentId)
                            // TODO:: add whereClause here were pivot.amount > fee_collections.amount
                            ;
                    })
                    ->with(['students' => function ($query) {
                        $query->where('student_id', $this->studentId);
                    }])
            )
            ->columns([
                ...$this->getCOlumns(),
            ])
            ->filters([
                //
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attendance records found.');
    }

    protected function getCOlumns()
    {
        $columns = ManageSchoolClassFeeCollections::getColumns();

        return [
            ...$columns,
        ];
    }

    public function render()
    {
        return view('livewire.student-fee-collections');
    }
}
