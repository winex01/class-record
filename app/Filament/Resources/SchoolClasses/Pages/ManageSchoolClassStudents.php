<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Students\Forms\StudentForm;
use App\Filament\Resources\Students\Filters\StudentFilters;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassStudentActions;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassStudentFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassStudentColumns;

class ManageSchoolClassStudents extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return SchoolClassStudentFilters::getTabs($this->getOwnerRecord());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(StudentForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns(SchoolClassStudentColumns::schema(['photo', 'full_name', 'gender', 'birth_date', 'email']))
            ->filters([StudentFilters::gender()])
            ->recordActions([
                ViewAction::make()->modalWidth(Width::Large),
                EditAction::make()->modalWidth(Width::Large),
                DetachAction::make()->color('warning'),
            ])
            ->toolbarActions([
                CreateAction::make()->label('New Student') ->modalWidth(Width::Large),
                SchoolClassStudentActions::attachAction(),
                SchoolClassStudentActions::detachBulkAction(),
            ]);
    }
}
