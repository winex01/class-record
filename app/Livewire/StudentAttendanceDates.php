<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\Column;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class StudentAttendanceDates extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

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
                Column::date('date'),

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
                    ->modalSubmitActionLabel(fn ($record) =>
                        $record->students->first()?->pivot->present ? 'Mark as Absent' : 'Mark as Present'
                    )
                    ->action(function ($record) {
                        $student = $record->students->first();
                        $currentState = $student?->pivot->present ?? false;
                        $newState = !$currentState;

                        $record->students()->updateExistingPivot($this->studentId, [
                            'present' => $newState,
                        ]);

                        // Dispatch event to refresh the overview table
                        $this->dispatch('refresh-attendance-overview-data');

                        // Show notification
                        Notification::make()
                            ->title('Attendance Updated')
                            ->body($student->full_name . ' marked as ' . ($newState ? 'Present' : 'Absent') . ' for ' . $record->date->format('M d, Y'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attendance records found.');
    }
}
