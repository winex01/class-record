<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\Resources\Attendances\RelationManagers\StudentsRelationManager;

class ManageSchoolClassAttendances extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'attendances';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Field::date('date')
                    ->columnSpanFull()
                    ->required()
                    ->date()
                    ->default(now())
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                Column::text('date'),

                Column::text('absents')
                    ->searchable(false)
                    ->badge()
                    ->color('danger')
                    ->state(fn ($record) => $record->students()->wherePivot('present', false)->count())
                    ->label('Absents'),

                Column::text('presents')
                    ->searchable(false)
                    ->badge()
                    ->color('success')
                    ->state(fn ($record) => $record->students()->wherePivot('present', true)->count())
                    ->label('Presents'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Medium)
                    ->after(function ($record, $data, $action) {
                        $record->students()->sync(SchoolClassResource::getClassStudents($this->getOwnerRecord()));
                    })
            ])
            ->recordActions([
                RelationManagerAction::make('take-attendance-relation-manager')
                    ->label('Take Attendance')
                    ->icon(\App\Services\Icon::students())
                    ->color('info')
                    ->slideOver()
                    ->relationManager(StudentsRelationManager::make()),

                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordAction('take-attendance-relation-manager');
    }
}
