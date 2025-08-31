<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClassStudents extends ManageRelatedRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected static string $relationship = 'students';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return StudentResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                ...StudentResource::getColumns()
            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Existing')
                    ->closeModalByClickingAway(false)
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectSearchColumns([
                        'last_name',
                        'first_name',
                        'middle_name',
                        'suffix_name',
                    ]),

                    CreateAction::make(),
            ])
            ->recordActions([
                // ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DetachAction::make()->color('warning'),
                // ])->grouped()
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
