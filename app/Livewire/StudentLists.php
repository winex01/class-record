<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Filament\Actions\BulkAction;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Actions\Contracts\HasActions;
use App\Filament\Traits\ManageActionVisibility;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class StudentLists extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;
    use ManageActionVisibility;

    public $schoolClass;
    public $schoolClassId;
    public array $attachedStudentsIds = [];

    public function mount(SchoolClass $schoolClass)
    {
        $this->schoolClass = $schoolClass;

        // This property it is use in ManageActionVisibility trait
        $this->schoolClassId = $schoolClass->id;

        // populate on mount from DB
        $this->attachedStudentsIds = $this->schoolClass
            ->students()
            ->pluck('students.id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        // Reset table page to 1 on mount or everytime modal is open
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->whereNotIn('id', $this->attachedStudentsIds)
            )
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...SchoolClassStudentColumns::schema(),
            ])
            ->filters([StudentFilters::gender()])
            ->bulkActions([
                BulkAction::make('attachSelectedStudents')
                    ->label('Attach Selected')
                    ->action(function () {
                        $selectedIds = $this->getSelectedTableRecords()->pluck('id');

                        $this->schoolClass
                            ->students()
                            ->syncWithoutDetaching($selectedIds);

                        $this->attachedStudentsIds = $this->schoolClass
                            ->students()
                            ->pluck('students.id')
                            ->map(fn($id) => (int) $id)
                            ->toArray();

                        Notification::make()
                            ->title('Students Attached')
                            ->body($selectedIds->count() . ' student(s) successfully enrolled.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalFooterActionsAlignment(Alignment::Center)
                    ->after(fn() => $this->resetTable()),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Student $record): bool => !in_array($record->id, $this->attachedStudentsIds)
            )
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }
}
