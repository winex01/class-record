<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use App\Exports\SchoolClassExport;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassExport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = SchoolClassResource::class;
    protected string $view = 'filament.resources.school-classes.pages.manage-school-class-export';
    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->record = SchoolClass::findOrFail($record);
        $this->form->fill();
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    // cache key per user per school class
    protected function cacheKey(): string
    {
        return 'export_preferences_user_' . auth()->id() . '_class_' . $this->record->id;
    }

    protected function getCached(string $field, mixed $default): mixed
    {
        return Cache::get($this->cacheKey() . "_{$field}", $default);
    }

    protected function putCached(string $field, mixed $value): void
    {
        // store for 30 days
        Cache::put($this->cacheKey() . "_{$field}", $value, now()->addDays(30));
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Export Options')
                    ->headerActions([
                        Action::make('generateExport')
                            ->label('Generate Export')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(fn() => $this->export()),
                    ])
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'lg' => 3,
                            'xl' => 3,
                        ])
                            ->schema([
                                $this->checkboxStudentColumns()->columnSpan(1),
                                $this->checkboxAttendanceCOlumns()->columnSpan(1),
                                $this->checkboxLessonColumns()->columnSpan(1),
                                $this->checkboxFeeCollectionColumns()->columnSpan(1),
                                $this->checkboxxGradeColumns()->columnSpan(1),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function sheetToggle(string $key, string $label): Toggle
    {
        return Toggle::make("{$key}_enabled")
            ->label($label)
            ->default($this->getCached("{$key}_enabled", true))
            ->inline(false)
            ->dehydrated(true)
            ->live()
            ->afterStateUpdated(function ($state) use ($key) {
                $this->putCached("{$key}_enabled", $state);
            });
    }

    public function checkboxStudentColumns()
    {
        return Section::make()
            ->schema([
                CheckboxList::make('student_columns')
                    ->label('Students Sheet')
                    ->options([
                        'full_name'      => 'Student Name',
                        'gender'         => 'Gender',
                        'birth_date'     => 'Birth Date',
                        'email'          => 'Email',
                        'contact_number' => 'Contact Number',
                    ])
                    ->default($this->getCached('student_columns', [
                        'full_name',
                        'gender',
                        'birth_date',
                        'email',
                        'contact_number',
                    ]))
                    ->disableOptionWhen(fn($value) => $value === 'full_name')
                    ->in(['full_name', 'gender', 'birth_date', 'email', 'contact_number'])
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (!in_array('full_name', $state ?? [])) {
                            $set('student_columns', array_merge($state ?? [], ['full_name']));
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return collect($state)->push('full_name')->unique()->values()->toArray();
                    })
                    ->afterStateUpdated(function ($state) {
                        $this->putCached('student_columns', $state);
                    })
                    ->live()
                    ->required(),
            ])
            ->compact();
    }

    public function checkboxAttendanceCOlumns()
    {
        return Section::make()
            ->schema([
                $this->sheetToggle('attendance', 'Attendance Sheet'),
                CheckboxList::make('attendance_columns')
                    ->label('Attendance Columns')
                    ->options([
                        'full_name' => 'Student Name',
                        'dates'     => 'Dates',
                        'present'   => 'Present',
                        'absent'    => 'Absent',
                    ])
                    ->default($this->getCached('attendance_columns', ['full_name', 'dates', 'present', 'absent']))
                    ->disableOptionWhen(fn($value) => $value === 'full_name')
                    ->in(['full_name', 'dates', 'present', 'absent'])
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (!in_array('full_name', $state ?? [])) {
                            $set('attendance_columns', array_merge($state ?? [], ['full_name']));
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return collect($state)->push('full_name')->unique()->values()->toArray();
                    })
                    ->afterStateUpdated(function ($state) {
                        $this->putCached('attendance_columns', $state);
                    })
                    ->live()
                    ->disabled(fn($get) => !$get('attendance_enabled'))
                    ->required(fn($get) => (bool) $get('attendance_enabled')),
            ])
            ->compact();
    }

    public function checkboxLessonColumns()
    {
        return Section::make()
            ->schema([
                $this->sheetToggle('lesson', 'Lessons Sheet'),
                CheckboxList::make('lesson_columns')
                    ->label('Lesson Columns')
                    ->options([
                        'title'           => 'Title',
                        'description'     => 'Description',
                        'tags'            => 'Tags',
                        'completion_date' => 'Completion',
                        'status'          => 'Status',
                        'checklists'      => 'Checklists',
                    ])
                    ->default($this->getCached('lesson_columns', [
                        'title',
                        'tags',
                        'completion_date',
                        'status',
                    ]))
                    ->afterStateUpdated(function ($state) {
                        $this->putCached('lesson_columns', $state);
                    })
                    ->live()
                    ->disabled(fn($get) => !$get('lesson_enabled'))
                    ->required(fn($get) => (bool) $get('lesson_enabled')),
            ])
            ->compact();
    }

    public function checkboxFeeCollectionColumns()
    {
        return Section::make()
            ->schema([
                $this->sheetToggle('fee_collection', 'Fee Collections Sheet'),
                CheckboxList::make('fee_collection_columns')
                    ->label('Fee Collection Columns')
                    ->options([
                        'full_name'       => 'Student Name',
                        'total_paid'      => 'Total Paid',
                        'total_remaining' => 'Total Remaining',
                    ])
                    ->default($this->getCached('fee_collection_columns', [
                        'full_name',
                        'total_paid',
                        'total_remaining',
                    ]))
                    ->disableOptionWhen(fn($value) => $value === 'full_name')
                    ->in(['full_name', 'total_paid', 'total_remaining'])
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (!in_array('full_name', $state ?? [])) {
                            $set('fee_collection_columns', array_merge($state ?? [], ['full_name']));
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return collect($state)->push('full_name')->unique()->values()->toArray();
                    })
                    ->afterStateUpdated(function ($state) {
                        $this->putCached('fee_collection_columns', $state);
                    })
                    ->live()
                    ->disabled(fn($get) => !$get('fee_collection_enabled'))
                    ->required(fn($get) => (bool) $get('fee_collection_enabled')),
            ])
            ->compact();
    }

    public function checkboxxGradeColumns()
    {
        return Section::make()
            ->schema([
                $this->sheetToggle('grade', 'Grades Sheet'),
                CheckboxList::make('grade_columns')
                    ->label('Grade Columns')
                    ->options(function () {
                        $baseOptions = ['full_name' => 'Student Name'];

                        if ($this->record?->gradeTransmutations()->exists()) {
                            $baseOptions['initial_grade']    = 'Initial Grade';
                            $baseOptions['transmuted_grade'] = 'Transmuted Grade';
                        } else {
                            $baseOptions['grade'] = 'Grade';
                        }

                        return $baseOptions;
                    })
                    ->default(function () {
                        $defaults = ['full_name'];

                        if ($this->record?->gradeTransmutations()->exists()) {
                            $defaults[] = 'initial_grade';
                            $defaults[] = 'transmuted_grade';
                        } else {
                            $defaults[] = 'grade';
                        }

                        return $this->getCached('grade_columns', $defaults);
                    })
                    ->disableOptionWhen(fn($value) => $value === 'full_name')
                    ->in(function () {
                        $valid = ['full_name'];

                        if ($this->record?->gradeTransmutations()->exists()) {
                            $valid[] = 'initial_grade';
                            $valid[] = 'transmuted_grade';
                        } else {
                            $valid[] = 'grade';
                        }

                        return $valid;
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (!in_array('full_name', $state ?? [])) {
                            $set('grade_columns', array_merge($state ?? [], ['full_name']));
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return collect($state)->push('full_name')->unique()->values()->toArray();
                    })
                    ->afterStateUpdated(function ($state) {
                        $this->putCached('grade_columns', $state);
                    })
                    ->live()
                    ->disabled(fn($get) => !$get('grade_enabled'))
                    ->required(fn($get) => (bool) $get('grade_enabled')),
            ])
            ->compact();
    }

    public function export()
    {
        $data = $this->form->getState();
        $yearSection = str_replace(',', '-', $this->record->year_section);

        return Excel::download(
            new SchoolClassExport($this->record, $data),
            "{$this->record->name}-{$yearSection}.xlsx"
        );
    }
}
