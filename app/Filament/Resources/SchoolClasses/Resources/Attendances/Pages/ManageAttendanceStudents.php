<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances\Pages;

use Filament\Tables\Table;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;
use App\Filament\Resources\SchoolClasses\Resources\Attendances\AttendanceResource;

class ManageAttendanceStudents extends ManageRelatedRecords
{

    protected static string $resource = AttendanceResource::class;

    protected static string $relationship = 'students';

    protected static ?string $relatedResource = AttendanceResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                ...ManageSchoolClassStudents::getColumns()
            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach students')
                    ->closeModalByClickingAway(false)
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectSearchColumns([
                        'last_name',
                        'first_name',
                        'middle_name',
                        'suffix_name',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                DetachAction::make()->color('warning'),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->color('warning')
                    ->action(function ($records, $livewire) {
                        /** @var \Filament\Resources\Pages\ManageRelatedRecords $livewire */
                        foreach ($records as $record) {
                            $livewire->getRelationship()->detach($record);
                        }
                    })

            ]);
    }
}
