<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use Filament\Actions\BulkAction;
use Filament\Support\Enums\Alignment;

class TakeAttendanceRelationActions
{
    public static function bulkMarkAbsentAction()
    {
        return
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
            ->modalFooterActionsAlignment(Alignment::Center)
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Marked as absent');
    }

    public static function bulkMarkPresentAction()
    {
        return
            BulkAction::make('markPresent')
            ->label('Mark Present')
            ->icon('heroicon-o-check-circle')
            ->color('primary')
            ->requiresConfirmation()
            ->action(function ($records, $livewire) {
                foreach ($records as $record) {
                    $livewire->getRelationship()->updateExistingPivot($record->id, ['present' => true]);
                }
            })
            ->modalFooterActionsAlignment(Alignment::Center)
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Marked as present');
    }
}
