<?php

namespace App\Livewire;

use App\Models\Group;
use Livewire\Component;
use App\Models\Assessment;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use App\Filament\Fields\Select;
use Filament\Support\Enums\Width;
use App\Filament\Fields\TextInput;
use Illuminate\Support\Facades\DB;
use App\Filament\Columns\TextColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use App\Livewire\Traits\RenderTableTrait;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use App\Filament\Traits\ManageActionVisibility;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Resources\Groups\Forms\GroupForm;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassAssessmentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassAssessmentColumns;

class StudentAssessmentLists extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;
    use ManageActionVisibility;

    public $studentId;
    public $schoolClassId;
    public $isReadOnly = false;

    public function mount($studentId, $schoolClassId)
    {
        $this->studentId = $studentId;
        $this->schoolClassId = $schoolClassId;
        $this->isReadOnly = !SchoolClass::findOrFail($this->schoolClassId)->active;

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
                ...$this->getCOlumns(),
            ])
            ->filters([
                SchoolClassAssessmentFilters::types(),

                SelectFilter::make('group')
                ->options(Group::query()->pluck('name', 'name'))
                ->multiple()
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        !empty($data['values']),
                        fn ($query) => $query->whereHas('students', function ($query) use ($data) {
                            $query->where('assessment_student.student_id', $this->studentId)
                                ->whereIn('assessment_student.group', $data['values']);
                        })
                    );
                })

            ])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attendance records found.')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }

    protected function getCOlumns()
    {
        $columns = SchoolClassAssessmentColumns::schema();
        $columns['max_score']->alignCenter();
        unset($columns['status']);

        return [
            ...$columns,

            TextColumn::make('students.pivot.score')
                ->color('success')
                ->label('Score')
                ->alignCenter()
                ->underline(!$this->isReadOnly)
                ->getStateUsing(function ($record) {
                    return $record->students->first()?->pivot->score;
                })
                ->searchable(false)
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query
                        ->orderBy(
                            DB::table('assessment_student')
                                ->select('score')
                                ->whereColumn('assessment_student.assessment_id', 'assessments.id')
                                ->where('assessment_student.student_id', $this->studentId)
                                ->limit(1),
                            $direction
                        );
                })
                ->action($this->isReadOnly ? null : $this->updateStudentScore()),

            TextColumn::make('students.pivot.group')
                ->label('Group')
                ->underline(fn ($record) => $record->can_group_students && !$this->isReadOnly)
                ->color(fn ($record) => $record->can_group_students ? 'info' : null)
                ->getStateUsing(function ($record) {
                    return $record->students->first()?->pivot->group;
                })
                ->searchable(false)
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query
                        ->orderBy(
                            DB::table('assessment_student')
                                ->select('group')
                                ->whereColumn('assessment_student.assessment_id', 'assessments.id')
                                ->where('assessment_student.student_id', $this->studentId)
                                ->limit(1),
                            $direction
                        );
                })
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->action($this->isReadOnly ? null : $this->updateStudentGroup()),
        ];
    }

    protected function updateStudentScore()
    {
        return Action::make('updateScore')
                ->form([
                    TextInput::make('score')
                        ->label('Score')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(fn ($record) => $record->max_score)
                        ->rules(fn ($record) => [
                            'numeric',
                            'min:0',
                            'max:' . $record->max_score
                        ])
                        ->default(fn ($record) => $record->students->first()?->pivot->score)
                        ->placeholder(fn ($record) => 'Max: ' . ($record->max_score))
                        ->helperText(fn ($record) => 'Maximum allowed score: ' . ($record->max_score)),
                ])
                ->action(function ($record, array $data) {
                    // Update only the score
                    $record->students()->updateExistingPivot($this->studentId, [
                        'score' => $data['score'],
                    ]);

                    Notification::make()
                        ->title('Score updated successfully')
                        ->success()
                        ->send();
                })
                ->modalHeading('Update Assessment Score')
                ->modalSubmitActionLabel('Save')
                ->modalWidth(Width::ExtraSmall);
    }

    protected function updateStudentGroup()
    {
        return Action::make('updateGroup')
                ->form([
                    Select::make('group')
                        ->label('Group')
                        ->options(function ($record) {
                            $baseOptions = GroupForm::selectOptions();

                            // Get current value and add it if it doesn't exist
                            $currentValue = $record->students->first()?->pivot->group ?? null;
                            if ($currentValue && !array_key_exists($currentValue, $baseOptions)) {
                                $baseOptions[$currentValue] = $currentValue;
                            }

                            return $baseOptions;
                        })
                        ->default(fn ($record) => $record->students->first()?->pivot->group)
                        ->afterStateUpdated(function ($state, $set) {
                            // If the state is null or empty, set it to '-'
                            if (empty($state)) {
                                $set('group', '-');
                            }
                        })
                ])
                ->action(function ($record, array $data) {
                    // If group is empty, set to '-'
                    $groupValue = empty($data['group']) ? '-' : $data['group'];

                    // Update only the group
                    $record->students()->updateExistingPivot($this->studentId, [
                        'group' => $groupValue,
                    ]);

                    Notification::make()
                        ->title('Group updated successfully')
                        ->success()
                        ->send();
                })
                ->modalHeading('Update Student Group')
                ->modalSubmitActionLabel('Save')
                ->modalWidth(Width::ExtraSmall)
                ->disabled(fn ($record) => !$record->can_group_students);
    }
}
