<?php

namespace App\Filament\Resources\SchoolClasses;

use App\Services\Icon;
use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
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
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClasses;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassGrades;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAssessments;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAttendances;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassFeeCollections;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Subject';

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return Icon::classes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Subject')
                    ->placeholder('e.g. Math 101 or ENG-201')
                    ->required()
                    ->maxLength(255),

                Field::tags('tags')
                    ->placeholder('e.g. 1st Year, Section A, Evening Class'),

                Grid::make(2)
                    ->schema([
                        Field::date('date_start')
                            ->label('Start Date')
                            ->placeholder('e.g. ' . Carbon::now()->format('M j, Y')), // e.g. Aug 28, 2025

                        Field::date('date_end')
                            ->label('End Date')
                            ->placeholder('e.g. ' . Carbon::now()->addMonths(6)->format('M j, Y')), // e.g. Nov 28, 2025
                    ]),

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name')->label('Subject'),

                Column::tags('tags'),

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
                )
            ])
            ->filters([
                TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Archived')
                    ->native(false)
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('manageSubject')
                        ->label('Manage Subject')
                        ->color('info')
                        ->url(fn ($record) => route('filament.app.resources.school-classes.students', $record))
                        ->icon(Icon::students()),

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

            NavigationItem::make('Assessments')
                ->url(ManageSchoolClassAssessments::getUrl(['record' => $record]))
                ->icon(Icon::assessments())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassAssessments),

            NavigationItem::make('Fee Collections')
                ->url(ManageSchoolClassFeeCollections::getUrl(['record' => $record]))
                ->icon(Icon::feeCollections())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassFeeCollections),

            NavigationItem::make('Grades')
                ->url(ManageSchoolClassGrades::getUrl(['record' => $record]))
                ->icon(Icon::grades())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassGrades),
        ];
    }
}
