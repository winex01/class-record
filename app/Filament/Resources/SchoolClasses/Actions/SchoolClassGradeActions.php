<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Enums\Gender;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use App\Filament\Fields\Select;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use App\Filament\Actions\ClearAction;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use App\Filament\Fields\ToggleButtons;
use App\Services\GradeComputationService;

class SchoolClassGradeActions
{
    public static function viewGradesAction(SchoolClass $ownerRecord)
    {
        return
            Action::make('grades')
            ->icon('heroicon-o-list-bullet')
            ->disabled(fn ($record) => ! $record->isComplete)
            ->tooltip(fn ($record) => ! $record->isComplete ? 'Please complete all grading components first.' : null)
            ->color(fn ($record) => ! $record->isComplete ? 'gray' : 'info')
            ->modalHeading(fn ($record) => $record->grading_period)
            ->modalDescription(new HtmlString(
                '💡 <strong>Tip:</strong> Hold <kbd style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem;">Shift</kbd> + scroll to navigate horizontally across all columns.'
            ))
            ->form(function () use ($ownerRecord) {
                return [
                    Grid::make(10)
                    ->schema([
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
                            ->suffixAction(ClearAction::make())
                            ->columnSpan(8),

                        ToggleButtons::make('group_by_gender')
                            ->label('Group by Gender')
                            ->live()
                            ->default(fn() => session('group_by_gender', false))
                            ->afterStateUpdated(fn($state) => session(['group_by_gender' => $state]))
                            ->options([
                                false => 'Ungroup',
                                true => 'Group',
                            ])
                            ->icons([
                                true  => 'heroicon-o-squares-2x2',  // Group
                                false => 'heroicon-o-squares-plus',  // Ungroup
                            ])
                            ->colors([
                                true => 'success',
                                false => 'gray',
                            ])
                            ->extraAttributes(['style' => 'position: relative; z-index: 50;'])
                            ->columnSpan(2),
                    ]),

                    View::make('filament.components.grades')
                        ->viewData(function ($get, $record) use ($ownerRecord) {
                            $studentFilter = $get('student_filter');
                            $isGroupByGender = $get('group_by_gender');
                            $schoolClass   = $ownerRecord;

                            $gradeService = new GradeComputationService($record, $studentFilter ?? []);

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

                            $students = collect();
                            $studentsGroupByGender = collect();

                            if ($isGroupByGender) {
                                $studentsGroupByGender = $studentsQuery->get()->groupBy('gender');
                            } else {
                                $students = $studentsQuery->get();
                            }

                            $totalColumns       = $gradeService->assessmentsByComponent()->sum(fn($a) => $a->count() + 3) + 2;
                            $hasTransmutedGrade = $schoolClass->gradeTransmutations()->exists();

                            return compact(
                                'record',
                                'schoolClass',
                                'gradeService',
                                'students',
                                'studentsGroupByGender',
                                'isGroupByGender',
                                'totalColumns',
                                'hasTransmutedGrade',
                            );
                        }),
                    ];
            })
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalAutofocus(false);
    }
}
