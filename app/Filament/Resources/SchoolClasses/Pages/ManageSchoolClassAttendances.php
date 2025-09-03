<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Services\Field;
use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                Column::text('date'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->modalWidth(Width::Medium),
            ])
            ->recordActions([
                Action::make('takeAttedance')
                    ->label('Take Attendance')
                    ->color('info')
                    ->icon(\App\Services\Icon::students())
                    ->url(fn ($record) => route(
                        'filament.app.resources.school-classes.attendances.attendance-students',
                        [
                            'school_class' => $record->school_class_id,
                            'record' => $record,
                        ])),

                EditAction::make()->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
