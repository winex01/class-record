<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class AssessmentOverview extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

    public $schoolClassId;

    public function mount($schoolClassId)
    {
        $this->schoolClassId = $schoolClassId;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->whereIn('id', SchoolClassResource::getStudents($this->schoolClassId))
            )
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns(SchoolClassStudentColumns::schema())
            ->filters([
                StudentFilters::gender()
            ])
            ->emptyStateHeading(false)
            ->emptyStateDescription(false)
            ->recordActions([
                Action::make('assessmentLists')
                    ->label('Assessments')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalHeading(fn ($record) => $record->full_name . ' - Assessments')
                    ->modalContent(fn ($record, $livewire) => new HtmlString(
                        Blade::render(
                            '@livewire("student-assessment-lists", ["studentId" => $studentId, "schoolClassId" => $schoolClassId])',
                            [
                                'studentId' => $record->id,
                                'schoolClassId' => $livewire->schoolClassId,
                            ]
                        )
                    ))
            ])
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }
}
