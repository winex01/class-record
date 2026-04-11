<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use Filament\Tables\Table;
use App\Filament\Fields\Select;
use Filament\Actions\BulkAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Grouping\Group;
use App\Filament\Resources\Groups\Schemas\GroupForm;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Filters\RecordScoreRelationFilters;
use App\Filament\Resources\SchoolClasses\Colulmns\RecordScoreRelationColumns;

class RecordScoreRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return RecordScoreRelationFilters::getTabs($this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns(RecordScoreRelationColumns::schema($this->getOwnerRecord()))
            ->filters(RecordScoreRelationFilters::filters($this->getOwnerRecord()))
            ->toolbarActions([
                BulkAction::make('assign_group')
                    ->label('Assign Group')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Select::make('group')
                            ->label('Group')
                            ->options(GroupForm::selectOptions())
                            ->required(),
                    ])
                    ->action(function ($records, array $data) {
                        $records->each(function ($record) use ($data) {
                            $record->pivot->group = $data['group'];
                            $record->pivot->save();
                        });
                    })
                    ->deselectRecordsAfterCompletion()
                    ->modalWidth(Width::Medium)
                    ->visible($this->getOwnerRecord()->can_group_students && $this->getOwnerRecord()->schoolClass->active)
            ])
            ->defaultGroup($this->getOwnerRecord()->can_group_students ? 'group' : null)
            ->groups(function () {
                if (!$this->getOwnerRecord()->can_group_students) {
                    return [];
                }

                return [
                    Group::make('group')
                        ->titlePrefixedWithLabel(false)
                        ->collapsible(),
                ];
            });

    }
}
