<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\ToggleColumn;
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
                    ->with(['students' => function ($query) {
                        $query->where('student_id', $this->studentId);
                    }])
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                IconColumn::make('present_status')
                    ->label('Status')
                    ->alignCenter()
                    ->state(function ($record) {
                        return $record->students->first()?->pivot->present ?? false;
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($state) => $state ? 'Present' : 'Absent'),
            ])
            ->recordActions([
                Action::make('toggle')
                    ->label(fn ($record) => $record->students->first()?->pivot->present ? 'Mark Absent' : 'Mark Present')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color(fn ($record) => $record->students->first()?->pivot->present ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Attendance Update')
                    ->modalDescription(fn ($record) =>
                        'Update attendance for ' . $record->date->format('M d, Y') . '?'
                    )
                    ->action(function ($record) {
                        $currentState = $record->students->first()?->pivot->present ?? false;
                        $record->students()->updateExistingPivot($this->studentId, [
                            'present' => !$currentState,
                        ]);

                        // Dispatch event to refresh the overview table
                        $this->dispatch('refresh-overview-data');

                    }),
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
