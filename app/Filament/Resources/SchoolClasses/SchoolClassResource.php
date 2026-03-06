<?php

namespace App\Filament\Resources\SchoolClasses;

use Filament\Tables\Table;
use App\Enums\LessonStatus;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Forms\SchoolClassForm;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClasses;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassColumns;
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
    protected static ?string $modelLabel = 'Class Subject';

    public static function getNavigationIcon(): string | \BackedEnum | Htmlable | null
    {
        return Heroicon::OutlinedRectangleGroup;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(SchoolClassForm::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns(SchoolClassColumns::schema())
            ->contentGrid(['md' => 3,'xl' => 3])
            ->recordActions([
                ActionGroup::make([
                    Action::make('manageClass')
                        ->tooltip('Manage Class')
                        ->label(false)
                        ->color('info')
                        ->url(fn ($record) => route('filament.app.resources.school-classes.students', $record))
                        ->icon(StudentResource::getNavigationIcon()),

                    SchoolClassActions::cloneAction(),

                    ViewAction::make()
                        ->modalWidth(Width::Large)
                        ->tooltip('View')
                        ->label(false),

                    EditAction::make()
                        ->modalWidth(Width::Large)
                        ->tooltip('Edit')
                        ->label(false),

                    DeleteAction::make()
                        ->tooltip('Delete')
                        ->label(false)
                        ->modalSubmitAction(fn ($action) => $action->color('danger')),
                ])
                ->buttonGroup()
                ->size(Size::Small)
            ])
            ->toolbarActions([
                CreateAction::make()->modalWidth(Width::Large),
                DeleteBulkAction::make(),
            ])
            ->recordUrl(fn ($record) => route('filament.app.resources.school-classes.students', $record));
    }

    public static function getStudents(SchoolClass|int $schoolClassOrId): array
    {
        $record = $schoolClassOrId;
        if (!$schoolClassOrId instanceof SchoolClass) {
            $record = SchoolClass::findOrFail($schoolClassOrId);
        }

        return $record->students()->pluck('students.id')->toArray();
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
                ->icon(StudentResource::getNavigationIcon())
                ->badge(fn () => $record->students()->count() ?: null, 'success')
                ->badgeTooltip('Total Students')
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassStudents),

            NavigationItem::make('Attendances')
                ->url(ManageSchoolClassAttendances::getUrl(['record' => $record]))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->badge(fn () => $record->attendances()->count() ?: null, 'success')
                ->badgeTooltip('Attendance Records')
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassAttendances),

            NavigationItem::make('Lessons')
                ->url(ManageSchoolClassLessons::getUrl(['record' => $record]))
                ->icon(Heroicon::OutlinedViewColumns)
                ->badge(fn () => $record->lessons()->whereNot('status', LessonStatus::DONE)->count() ?: null, 'success')
                ->badgeTooltip('Remaining Lessons')
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassLessons),

            NavigationItem::make('Assessments')
                ->url(ManageSchoolClassAssessments::getUrl(['record' => $record]))
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->badge(fn () => $record->assessments()->count() ?: null, 'success')
                ->badgeTooltip('Number of Assessments')
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassAssessments),

            NavigationItem::make('Fee Collections')
                ->url(ManageSchoolClassFeeCollections::getUrl(['record' => $record]))
                ->icon(Heroicon::OutlinedCircleStack)
                ->badge(fn () => $record->feeCollections()->count() ?: null, 'success')
                ->badgeTooltip('Number of Collections')
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassFeeCollections),

            NavigationItem::make('Grades')
                ->url(ManageSchoolClassGrades::getUrl(['record' => $record]))
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->badge(fn () => $record->grades()->count() ?: null, 'success')
                ->badgeTooltip('Grading Periods')
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassGrades),
        ];
    }
}
