<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Assessment;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Livewire\Traits\RenderTableTrait;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\Groups\GroupResource;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAssessments;

class StudentAssessmentLists extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use RenderTableTrait;

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
                ...$this->getCOlumns(),
            ])
            ->filters([
                ...ManageSchoolClassAssessments::getFilters(),
            ])
            ->paginated([10, 25, 50])
            ->emptyStateHeading('No Records')
            ->emptyStateDescription('No attendance records found.');
    }

    protected function getCOlumns()
    {
        $columns = ManageSchoolClassAssessments::getColumns();
        $columns['max_score']->alignCenter();

        return [
            ...$columns,

            TextColumn::make('students.pivot.score')
                ->badge()
                ->color('success')
                ->label('Score')
                ->getStateUsing(function ($record) {
                    return $record->students->first()?->pivot->score;
                })
                ->alignCenter()
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
                ->action($this->updateStudentScore()),

            TextColumn::make('students.pivot.group')
                ->label('Group')
                ->badge(fn ($record) => $record->can_group_students)
                ->color(fn ($record) => $record->can_group_students ? 'info' : null)
                ->getStateUsing(function ($record) {
                    return $record->students->first()?->pivot->group;
                })
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query
                        ->orderBy(
                            \DB::table('assessment_student')
                                ->select('group')
                                ->whereColumn('assessment_student.assessment_id', 'assessments.id')
                                ->where('assessment_student.student_id', $this->studentId)
                                ->limit(1),
                            $direction
                        );
                })
                ->toggleable()
                ->toggledHiddenByDefault(true)
                ->action($this->updateStudentGroup()),
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
                        ->maxValue(fn ($record) => $record->max_score ?? 100)
                        ->rules(fn ($record) => [
                            'numeric',
                            'min:0',
                            'max:' . ($record->max_score ?? 100)
                        ])
                        ->default(fn ($record) => $record->students->first()?->pivot->score)
                        ->placeholder(fn ($record) => 'Max: ' . ($record->max_score ?? 0))
                        ->helperText(fn ($record) => 'Maximum allowed score: ' . ($record->max_score ?? 100)),
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
                            $baseOptions = GroupResource::selectOptions();

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
                        ->searchable()
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
                ->visible(fn ($record) => $record->can_group_students);
    }
}
