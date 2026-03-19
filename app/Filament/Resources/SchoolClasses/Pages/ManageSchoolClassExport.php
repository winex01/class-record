<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\SchoolClass;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use App\Exports\SchoolClassExport;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
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

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Export Options')
                    ->schema([
                        Section::make('Export Options')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                    'lg' => 3,
                                    'xl' => 4,
                                ])
                                    ->schema([
                                        $this->checkboxStudentColumns()->columnSpan(1),
                                        $this->checkboxAttendanceCOlumns()->columnSpan(1),
                                        // TODO:: add lesson
                                        // TODO:: fee collection
                                        $this->checkboxxGradeColumns()->columnSpan(1),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function checkboxStudentColumns()
    {
        return
            CheckboxList::make('student_columns')
                ->label('Student Columns')
                ->options([
                    'full_name' => 'Student Name',
                    'gender' => 'Gender',
                    'birth_date' => 'Birth Date',
                    'email' => 'Email',
                    'contact_number' => 'Contact Number',
                ])
                ->default([
                    'full_name',
                    'gender',
                    'birth_date',
                    'email',
                    'contact_number',
                ])
                ->disableOptionWhen(fn($value) => $value === 'full_name')
                ->in([
                    'full_name',
                    'gender',
                    'birth_date',
                    'email',
                    'contact_number',
                ])
                ->afterStateHydrated(function ($state, callable $set) {
                    if (!in_array('full_name', $state ?? [])) {
                        $set('student_columns', array_merge($state ?? [], ['full_name']));
                    }
                })
                ->dehydrateStateUsing(function ($state) {
                    return collect($state)->push('full_name')->unique()->values()->toArray();
                })
                ->required();
    }

    public function checkboxAttendanceCOlumns()
    {
        return
            CheckboxList::make('attendance_columns')
                ->label('Attendance Columns')
                ->options([
                    'full_name' => 'Student Name',
                    'dates' => 'Dates',
                    'present' => 'Present',
                    'absent' => 'Absent',
                ])
                ->default(['full_name', 'dates', 'present', 'absent'])
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
                ->required();
    }

    public function checkboxxGradeColumns()
    {
        return
            CheckboxList::make('grade_columns')
                ->label('Grade Columns')
                ->options(function () {
                    $baseOptions = [
                        'full_name' => 'Student Name',
                    ];

                    if ($this->record?->gradeTransmutations()->exists()) {
                        $baseOptions['initial_grade'] = 'Initial Grade';
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

                    return $defaults;
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
                ->required();
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
