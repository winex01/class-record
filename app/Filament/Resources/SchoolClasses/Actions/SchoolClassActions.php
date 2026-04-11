<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Enums\LessonStatus;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Schemas\SchoolClassForm;

class SchoolClassActions
{
    public static function createWithStudentsAction($getOwnerRecord)
    {
        return
            CreateAction::make()
            ->after(function ($livewire, $record) use ($getOwnerRecord) {
                $record->students()->sync(SchoolClassResource::getStudents($getOwnerRecord));
                $livewire->dispatch('refreshCollapsibleTableWidget');
            });
    }

    public static function cloneAction()
    {
        return
            Action::make('clone')
            ->color('warning')
            ->icon('heroicon-o-document-duplicate')
            ->modalHeading(fn ($record) => 'Clone Class: ' . $record->name)
            ->schema([
                CheckboxList::make('items_to_clone')
                    ->label('Select items to include in clone')
                    ->options([
                        'students' => 'Students',
                        'lessons' => 'Lessons',
                        'assessments' => 'Assessments',
                        'grading_settings' => 'Grading Settings',
                    ])
                    ->default(['students', 'lessons', 'assessments', 'grading_settings'])
                    ->required()
                    ->columns(2),

                SchoolClassForm::getFields()['name']->default(fn ($record) => $record->name),
                SchoolClassForm::getFields()['year_section']->default(fn ($record) => $record->year_section ?? []),
                SchoolClassForm::getFields()['date_start']->default(fn ($record) => $record->date_start),
                SchoolClassForm::getFields()['date_end']->default(fn ($record) => $record->date_end),

            ])
            ->action(function (array $data, $record) {
                // Start a database transaction for data integrity
                DB::beginTransaction();

                try {
                    // Clone the main class record
                    $newClass = $record->replicate();
                    $newClass->name = $data['name'];
                    $newClass->year_section = $data['year_section'];
                    $newClass->date_start = $data['date_start'];
                    $newClass->date_end = $data['date_end'];
                    $newClass->active = true;
                    $newClass->save();

                    $itemsToClone = $data['items_to_clone'];

                    // Clone Students
                    if (in_array('students', $itemsToClone) && $record->students()->exists()) {
                        $studentIds = $record->students()->pluck('students.id');
                        $newClass->students()->attach($studentIds);
                    }

                    // Clone Lessons
                    if (in_array('lessons', $itemsToClone) && $record->lessons()->exists()) {
                        foreach ($record->lessons as $lesson) {
                            $newLesson = $lesson->replicate(['tags_search']); // exclude tags_seaerch column as it is mysql virtualColumn
                            $newLesson->school_class_id = $newClass->id;
                            $newLesson->status = LessonStatus::TOPICS->value;
                            $newLesson->save();

                            // Clone the many-to-many relationship with MyFile
                            if ($lesson->myFiles()->exists()) {
                                // Get the IDs of related MyFile records (with pivot data if needed)
                                $myFileIds = $lesson->myFiles()->pluck('my_files.id')->toArray();

                                // Attach the same MyFile records to the new lesson
                                $newLesson->myFiles()->attach($myFileIds);
                            }
                        }
                    }

                    // Clone Assessments
                    if (in_array('assessments', $itemsToClone) && $record->assessments()->exists()) {
                        foreach ($record->assessments as $assessment) {
                            $newAssessment = $assessment->replicate();
                            $newAssessment->school_class_id = $newClass->id;
                            $newAssessment->save();

                            // Clone assessment students
                            if ($assessment->students()->exists()) {
                                $studentIds = $assessment->students()->pluck('students.id');
                                $newAssessment->students()->attach($studentIds);
                            }
                        }
                    }

                    // Clone Grading Settings
                    // NOTE:: although GradingComponent and Transmutation is using the grading_settings checkbox but they have 2 different class
                    if (in_array('grading_settings', $itemsToClone) && $record->gradingComponents()->exists()
                        && $record->gradeTransmutations()->exists()) {

                        // grading components
                        foreach ($record->gradingComponents as $gradingComponent) {
                            $newGradingComponent = $gradingComponent->replicate();
                            $newGradingComponent->school_class_id = $newClass->id;
                            $newGradingComponent->save();
                        }

                        // grade transmutation table
                        foreach ($record->gradeTransmutations as $gradeTransmutation) {
                            $newGradeTransmutation = $gradeTransmutation->replicate();
                            $newGradeTransmutation->school_class_id = $newClass->id;
                            $newGradeTransmutation->save();
                        }
                    }

                    DB::commit();

                    Notification::make()
                        ->title('Class cloned successfully')
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    DB::rollBack();

                    Notification::make()
                        ->title('Error cloning class')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalWidth(Width::Large);
    }
}
