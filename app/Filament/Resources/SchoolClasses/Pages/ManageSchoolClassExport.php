<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Models\SchoolClass;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use App\Exports\SchoolClassExport;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
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

    public function mount(int | string $record): void
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
                        CheckboxList::make('student_columns')
                        ->label('Student Columns')
                        ->options([
                            'full_name'      => 'Student Name',
                            'gender'         => 'Gender',
                            'birth_date'     => 'Birth Date',
                            'email'          => 'Email',
                            'contact_number' => 'Contact Number',
                        ])
                        ->required()
                        ->default(['full_name', 'gender', 'birth_date', 'email', 'contact_number']),
                    ]),
            ])
            ->statePath('data');
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
