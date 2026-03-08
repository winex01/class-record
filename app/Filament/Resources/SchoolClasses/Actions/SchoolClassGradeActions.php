<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Models\SchoolClass;
use Filament\Actions\Action;
use App\Filament\Fields\Select;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Actions\ClearAction;
use Filament\Schemas\Components\View;

class SchoolClassGradeActions
{
    public static function viewGradesAction(SchoolClass $ownerRecord)
    {
        return
            Action::make('grades')
            ->icon('heroicon-o-list-bullet')
            ->color('info')
            ->modalHeading(fn ($record) => $record->grading_period)
            ->modalDescription(new HtmlString(
                '💡 <strong>Tip:</strong> Hold <kbd style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem;">Shift</kbd> + scroll to navigate horizontally across all columns.'
            ))
            ->form(function () use ($ownerRecord) {
                return [
                    Select::make('student_filter')
                        ->label('Filter Student')
                        ->placeholder('All Students')
                        ->options(function () use ($ownerRecord) {
                            return $ownerRecord->students()
                                ->orderBy('last_name')->orderBy('first_name')
                                ->get()->pluck('full_name', 'id')
                                ->toArray();
                        })
                        ->native(false)
                        ->live()
                        ->multiple()
                        ->extraAttributes(['style' => 'position: relative; z-index: 50;'])
                        ->suffixAction(ClearAction::make()),

                        View::make('filament.components.grades')
                            ->viewData(function ($get, $record) use ($ownerRecord) {
                                $studentFilter = $get('student_filter');
                                $schoolClass = $ownerRecord;

                                // OPTIMIZATION 1: Eager load all relationships upfront
                                $gradeGradingComponents = $record->orderedGradeGradingComponents()
                                    ->with([
                                        'gradingComponent',
                                        'assessments' => function ($query) use ($studentFilter) {
                                            // Only load student scores we need
                                            $query->with(['students' => function ($q) use ($studentFilter) {
                                                if (!empty($studentFilter)) {
                                                    $q->whereIn('students.id', $studentFilter);
                                                }
                                                // Select the actual columns that build the accessor
                                                // Adjust these column names to match your actual database columns
                                                $q->select('students.id', 'students.first_name', 'students.last_name', 'students.middle_name', 'students.suffix_name', 'students.gender');
                                            }]);
                                        }
                                    ])
                                    ->get();

                                $groupedAssessments = $gradeGradingComponents
                                    ->groupBy(fn($ggc) => $ggc->gradingComponent?->label)
                                    ->map(fn($group) => $group->flatMap->assessments);

                                // OPTIMIZATION 2: Pre-calculate assessment totals
                                $assessmentMeta = [];
                                foreach ($groupedAssessments as $label => $assessments) {
                                    $totalScore = 0;
                                    $firstAssessment = $assessments->first();
                                    $gradeGradingComponent = $firstAssessment->gradeGradingComponents->first();

                                    foreach ($assessments as $assessment) {
                                        $totalScore += $assessment->max_score;
                                    }

                                    $assessmentMeta[$label] = [
                                        'total_score' => $totalScore,
                                        'weighted_score' => $gradeGradingComponent->gradingComponent->weighted_score,
                                        'weighted_score_label' => $gradeGradingComponent->gradingComponent->weighted_score_percentage_label,
                                        'component_label' => $gradeGradingComponent->gradingComponent->name,
                                    ];
                                }

                                // OPTIMIZATION 3: Pre-process student scores into a lookup array
                                $studentScores = [];
                                foreach ($groupedAssessments as $label => $assessments) {
                                    foreach ($assessments as $assessment) {
                                        foreach ($assessment->students as $student) {
                                            $studentScores[$student->id][$assessment->id] = $student->pivot->score ?? null;
                                        }
                                    }
                                }

                                // Filter students - select actual columns, not accessors
                                $studentsQuery = $schoolClass->students()
                                    ->select(
                                        'students.id',
                                        'students.first_name',
                                        'students.last_name',
                                        'students.middle_name',
                                        'students.suffix_name',
                                        'students.gender',
                                        'students.photo',
                                    );

                                if (!empty($studentFilter)) {
                                    $studentsQuery->whereIn('students.id', $studentFilter);
                                }

                                $students = $studentsQuery->get()->groupBy('gender');

                                $totalAssessmentColumns = $groupedAssessments->sum(fn($assessments) => $assessments->count() + 3);
                                $totalColumns = $totalAssessmentColumns + 2;
                                $percentageScore = 100;
                                $hasTransmutedGrade = $schoolClass->gradeTransmutations()->exists();

                                return compact(
                                    'record',
                                    'schoolClass',
                                    'gradeGradingComponents',
                                    'groupedAssessments',
                                    'totalAssessmentColumns',
                                    'totalColumns',
                                    'students',
                                    'percentageScore',
                                    'studentFilter',
                                    'hasTransmutedGrade',
                                    'assessmentMeta',
                                    'studentScores'
                                );
                            }),
                    ];
            })
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalAutofocus(false);
    }
}
