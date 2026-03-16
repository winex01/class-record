<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Resources\Pages\Page;
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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
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
                        Select::make('format')
                            ->label('File Format')
                            ->options([
                                'xlsx' => 'Excel (.xlsx)',
                                'csv'  => 'CSV (.csv)',
                            ])
                            ->default('xlsx'),

                        CheckboxList::make('columns')
                            ->label('Columns to Include')
                            ->options([
                                'full_name'      => 'Student Name',
                                'gender'         => 'Gender',
                                'birth_date'     => 'Birth Date',
                                'email'          => 'Email',
                                'contact_number' => 'Contact Number',
                            ])
                            ->default(['full_name', 'gender']),
                    ]),
            ]);
    }

    public function export(): void
    {
        $data = $this->form->getState();

        // handle export with $data
        dd($data);
    }
}
