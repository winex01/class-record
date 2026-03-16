<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use App\Filament\Fields\DatePicker;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Traits\ManageSchoolClassInitTrait;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassActions;
use App\Filament\Resources\SchoolClasses\Actions\SchoolClassAttendanceActions;
use App\Filament\Resources\SchoolClasses\Filters\SchoolClassAttendanceFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\SchoolClassAttendanceColumns;

class ManageSchoolClassAttendances extends ManageRelatedRecords
{
    use ManageSchoolClassInitTrait;

    protected static string $resource = SchoolClassResource::class;
    protected static string $relationship = 'attendances';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required()
                    ->date()
                    ->default(now())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->defaultSort('date', 'desc')
            ->columns(SchoolClassAttendanceColumns::schema())
            ->filters([SchoolClassAttendanceFilters::dateRange()])
            ->recordActions([
                SchoolClassAttendanceActions::takeAttendanceAction(),
                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make()
                     ->recordTitle(fn ($record) => $record->date->format('M d, Y')),
            ])
            ->toolbarActions([
                SchoolClassActions::createWithStudentsAction($this->getOwnerRecord())
                    ->label('New Attendance')
                    ->modalWidth(Width::Medium),

                SchoolClassAttendanceActions::overviewAction(),

                DeleteBulkAction::make(),
            ])
            ->recordAction('takeAttendanceRelationManager');
    }
}
