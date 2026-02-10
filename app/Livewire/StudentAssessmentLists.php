<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Assessment;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAssessments;

class StudentAssessmentLists extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $studentId;
    public $schoolClassId;

    public function mount($studentId, $schoolClassId)
    {
        $this->studentId = $studentId;
        $this->schoolClassId = $schoolClassId;

        // Reset table page to 1 on mount or everytime modal is open
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->query(
                Assessment::query()
                    ->where('school_class_id', $this->schoolClassId)
                    ->whereHas('students', function ($query) {
                        $query->where('student_id', $this->studentId);
                    })
                    ->with(['students' => function ($query) {
                        $query->where('student_id', $this->studentId);
                    }])
            )
            ->columns([
                ...ManageSchoolClassAssessments::getColumns(),

                TextColumn::make('students.pivot.score')
                    ->label('Score')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->students->first()?->pivot->score;
                    }),

                TextColumn::make('students.pivot.group')
                    ->label('Group')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->students->first()?->pivot->group;
                    }),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attendance records found.');
    }

    public function render()
    {
        return view('livewire.student-assessment-lists');
    }
}
