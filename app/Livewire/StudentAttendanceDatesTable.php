<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class StudentAttendanceDatesTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $studentId;
    public $schoolClassId;
    public $isPresent; // true for present, false for absent

    public function mount($studentId, $schoolClassId, $isPresent)
    {
        $this->studentId = $studentId;
        $this->schoolClassId = $schoolClassId;
        $this->isPresent = $isPresent;

        // Reset table page to 1 on mount or everytime modal is open
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->where('school_class_id', $this->schoolClassId)
                    ->whereHas('students', function ($query) {
                        $query->where('student_id', $this->studentId)
                              ->where('present', $this->isPresent);
                    })
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('F d, Y')
                    ->sortable(),
            ])
            ->recordActions([
                //
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attendance records found.');
    }

    public function render()
    {
        return view('livewire.student-attendance-dates-table');
    }
}
