<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use App\Services\Column;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class TakeAttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->count()
                ),

            'present' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('present', true))
                ->badgeColor('info')
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('present', true)->count()
                ),

            'absent' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('present', false))
                ->badgeColor('danger')
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('present', false)->count()
                )
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                // when active
                ToggleColumn::make('present')
                    ->offColor('danger')
                    ->width('1%')
                    ->alignCenter()
                    ->sortable()
                    ->visible(fn () => $this->getOwnerRecord()->schoolClass->active),

                // when archived
                Column::icon('present_icon')
                    ->label('Present')
                    ->getStateUsing(fn ($record) => $record->present)
                    ->visible(fn () => !$this->getOwnerRecord()->schoolClass->active),

            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                ManageSchoolClassStudents::attachAction($this->getOwnerRecord()),
            ])
            ->toolbarActions([
                BulkAction::make('markAbsent')
                    ->label('Mark Absent')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records, $livewire) {
                        foreach ($records as $record) {
                            $livewire->getRelationship()->updateExistingPivot($record->id, ['present' => false]);
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Marked as absent'),

                BulkAction::make('markPresent')
                    ->label('Mark Present')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records, $livewire) {
                        foreach ($records as $record) {
                            $livewire->getRelationship()->updateExistingPivot($record->id, ['present' => true]);
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Marked as present'),

                ManageSchoolClassStudents::detachBulkAction(),
            ]);
    }
}
