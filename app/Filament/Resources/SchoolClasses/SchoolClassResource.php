<?php

namespace App\Filament\Resources\SchoolClasses;

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
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClasses;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassAttendances;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Class';

    public static function getNavigationIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return \App\Services\Icon::classes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Enter the basic information about this class or subject name.')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->label('Class name')
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
                            ->placeholder('Brief details about this class or subject... (optional)')
                            ->rows(5),

                        Toggle::make('active')
                            ->label('Active / Archived')
                            ->helperText('Active = editable, Archived = read-only')
                            ->offColor('danger')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-lock-closed')
                            ->default(true),

                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Column::text('name')->label('Class name'),

                Column::tags('tags'),

                Column::text('date_start'),

                Column::text('date_end'),

                Column::text('description')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('active')
                    ->toggleable(isToggledHiddenByDefault:false)
                    ->boolean()
                    ->width('1%')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('manage-class')
                        ->label('Manage Class')
                        ->color('info')
                        ->url(fn ($record) => route('filament.app.resources.school-classes.students', $record))
                        ->icon(\App\Services\Icon::students()),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->grouped()
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordUrl(fn ($record) => route('filament.app.resources.school-classes.students', $record));
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSchoolClasses::route('/'),
            'students' => ManageSchoolClassStudents::route('/{record}/students'),
            'attendances' => ManageSchoolClassAttendances::route('/{record}/attendances'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();

        return [
            NavigationItem::make('Students')
                ->url(ManageSchoolClassStudents::getUrl(['record' => $record]))
                ->icon(\App\Services\Icon::students())
                ->badge($record->students()->count())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassStudents),

            'attendances' =>
            NavigationItem::make('Attendances')
                ->url(ManageSchoolClassAttendances::getUrl(['record' => $record]))
                ->icon(\App\Services\Icon::attendances())
                ->isActiveWhen(fn () => $page instanceof ManageSchoolClassAttendances),
        ];
    }

    public static function getClassStudents($record)
    {
        return $record->students()->pluck('students.id')->toArray();
    }
}
