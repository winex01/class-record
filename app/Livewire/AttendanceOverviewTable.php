<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use App\Models\Attendance;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class AttendanceOverviewTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public $schoolClassId;
    public $studentsData = [];

    public function mount($schoolClassId, $studentsData)
    {
        $this->schoolClassId = $schoolClassId;
        $this->studentsData = $studentsData;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Student::query()->whereIn('id', array_column($this->studentsData, 'id')))
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                TextColumn::make('present_count')
                ->label('Present')
                ->state(function ($record) {
                    $studentData = collect($this->studentsData)->firstWhere('id', $record->id);
                    return $studentData['present_count'] ?? 0;
                })
                ->alignCenter()
                ->badge()
                ->color('success')
                ->sortable(query: function ($query, $direction) {
                    $studentsData = $this->studentsData;
                    $orderMap = collect($studentsData)->sortBy('present_count', SORT_REGULAR, $direction === 'desc')->pluck('id')->toArray();

                    return $query->orderByRaw('FIELD(id, ' . implode(',', $orderMap) . ')');
                })
                ->action(
                    Action::make('viewPresences')
                        ->modalHeading(fn ($record) => 'Present Dates - ' . $record->name)
                        ->modalContent(fn ($record, $livewire) => view('filament.components.student-attendance-dates', [
                            'studentId' => $record->id,
                            'schoolClassId' => $livewire->schoolClassId,
                            'isPresent' => true,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalWidth('2xl')
                ),

                TextColumn::make('absent_count')
                ->label('Absent')
                ->state(function ($record) {
                    $studentData = collect($this->studentsData)->firstWhere('id', $record->id);
                    return $studentData['absent_count'] ?? 0;
                })
                ->alignCenter()
                ->badge()
                ->color('danger')
                ->sortable(query: function ($query, $direction) {
                    $studentsData = $this->studentsData;
                    $orderMap = collect($studentsData)->sortBy('absent_count', SORT_REGULAR, $direction === 'desc')->pluck('id')->toArray();

                    return $query->orderByRaw('FIELD(id, ' . implode(',', $orderMap) . ')');
                })
                ->action(
                    Action::make('viewAbsences')
                        ->modalHeading(fn ($record) => 'Absent Dates - ' . $record->name)
                        ->modalContent(fn ($record, $livewire) => view('filament.components.student-attendance-dates', [
                            'studentId' => $record->id,
                            'schoolClassId' => $livewire->schoolClassId,
                            'isPresent' => false,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalWidth('2xl')
                ),
            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false);
    }

    public function render()
    {
        return view('livewire.attendance-overview-table');
    }
}
