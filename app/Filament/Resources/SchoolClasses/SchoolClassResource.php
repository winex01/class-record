<?php

namespace App\Filament\Resources\SchoolClasses;

use App\Services\Icon;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use App\Enums\LessonStatus;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClasses;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassGrades;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassLessons;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAssessments;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAttendances;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassFeeCollections;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Class';

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return Icon::classes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...static::formSchema(),
            ]);
    }

    public static function formSchema()
    {
        return [
            'name' =>
            TextInput::make('name')
                    ->label('Subject')
                    ->placeholder('e.g. Math 101 or ENG-201')
                    ->required()
                    ->maxLength(255),

            'year_section' =>
            Field::tags('year_section')
                ->placeholder('e.g. 1st Year, Grade 1, Section A'),

            'date_start' =>
            Field::date('date_start')
                ->label('Start Date')
                ->placeholder('e.g. ' . Carbon::now()->format('M j, Y')), // e.g. Aug 28, 2025

            'date_end' =>
            Field::date('date_end')
                ->label('End Date')
                ->placeholder('e.g. ' . Carbon::now()->addMonths(6)->format('M j, Y')), // e.g. Nov 28, 2025

            'description' =>
            Textarea::make('description')
                ->label('Description')
                ->placeholder('Brief details about this subject... (optional)')
                ->rows(5),

            Field::toggleBoolean('active')
                ->default(true)
                ->label('Status')
                ->helperText('Active = editable, Archived = view only')
                ->options([
                    true => 'Active',
                    false => 'Archived',
                ])
                ->icons([
                    true => 'heroicon-o-check',
                    false => 'heroicon-o-lock-closed',
                ])
                ->colors([
                    true => 'success',
                    false => 'warning',
                ])
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name')->label('Subject'),
                Column::tags('year_section'),
                Column::date('date_start'),
                Column::date('date_end'),
                Column::text('description')
                    ->toggleable(isToggledHiddenByDefault: true),
                Column::boolean(
                    name: 'active',
                    trueLabel: 'Active',
                    falseLabel: 'Archived',
                    falseIcon: 'heroicon-o-lock-closed',
                    falseColor: 'warning'
                )->label('Status')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('manageClass')
                        ->label('Manage Class')
                        ->color('info')
                        ->url(fn ($record) => route('filament.app.resources.school-classes.students', $record))
                        ->icon(Icon::students()),

                    static::cloneClassAction(),

                    ViewAction::make()->modalWidth(Width::Large),
                    EditAction::make()->modalWidth(Width::Large),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordUrl(fn ($record) => route('filament.app.resources.school-classes.students', $record));
    }

    private static function cloneClassAction()
    {
        return Action::make('clone')
                ->label('Clone Class')
                ->color('warning')
                ->icon('heroicon-o-document-duplicate')
                ->modalHeading(fn ($record) => 'Clone Class: ' . $record->name)
                ->form([
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

                    static::formSchema()['name']->default(fn ($record) => $record->name),
                    static::formSchema()['year_section']->default(fn ($record) => $record->year_section ?? []),
                    static::formSchema()['date_start']->default(fn ($record) => $record->date_start),
                    static::formSchema()['date_end']->default(fn ($record) => $record->date_end),

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
                                $newLesson = $lesson->replicate();
                                $newLesson->school_class_id = $newClass->id;
                                $newLesson->status = LessonStatus::TOPICS->value;
                                $newLesson->save();
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

    public static function getClassStudents($recordOrId)
    {
        $record = $recordOrId;
        if (!$recordOrId instanceof SchoolClass) {
            $record = SchoolClass::findOrFail($recordOrId)->first();
        }

        return $record->students()->pluck('students.id')->toArray();
    }

    public static function createAction($getOwnerRecord)
    {
        return \Filament\Actions\CreateAction::make()
            ->after(function ($record) use ($getOwnerRecord) {
                $record->students()->sync(static::getClassStudents($getOwnerRecord));
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSchoolClasses::route('/'),
            'students' => ManageSchoolClassStudents::route('/{record}/students'),
            'attendances' => ManageSchoolClassAttendances::route('/{record}/attendances'),
            'assessments' => ManageSchoolClassAssessments::route('/{record}/assessments'),
            'fee-collections' => ManageSchoolClassFeeCollections::route('/{record}/fee-collections'),
            'grades' => ManageSchoolClassGrades::route('/{record}/grades'),
            'lessons' => ManageSchoolClassLessons::route('/{record}/lessons'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();

        return [
            NavigationItem::make('Students')
                ->url(ManageSchoolClassStudents::getUrl(['record' => $record]))
                ->icon(Icon::students())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassStudents),

            NavigationItem::make('Attendances')
                ->url(ManageSchoolClassAttendances::getUrl(['record' => $record]))
                ->icon(Icon::attendances())
                // ->badge($record->attendances()->count())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassAttendances),

            NavigationItem::make('Lessons')
                ->url(ManageSchoolClassLessons::getUrl(['record' => $record]))
                ->icon(Icon::lessons())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassLessons),

            NavigationItem::make('Assessments')
                ->url(ManageSchoolClassAssessments::getUrl(['record' => $record]))
                ->icon(Icon::assessments())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassAssessments),

            NavigationItem::make('Grades')
                ->url(ManageSchoolClassGrades::getUrl(['record' => $record]))
                ->icon(Icon::grades())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassGrades),

            NavigationItem::make('Fee Collections')
                ->url(ManageSchoolClassFeeCollections::getUrl(['record' => $record]))
                ->icon(Icon::feeCollections())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassFeeCollections),


        ];
    }
}
