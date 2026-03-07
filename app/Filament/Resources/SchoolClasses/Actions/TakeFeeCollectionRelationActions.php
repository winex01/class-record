<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Models\FeeCollection;
use Filament\Actions\BulkAction;
use App\Enums\FeeCollectionStatus;
use Filament\Support\Enums\Alignment;

class TakeFeeCollectionRelationActions
{
    public static function bulkMarkPaidAction(FeeCollection $ownerRecord)
    {
        return
            BulkAction::make('markPaid')
                ->label('Mark as Paid')
                ->icon(FeeCollectionStatus::PAID->getIcon())
                ->color(FeeCollectionStatus::PAID->getColor())
                ->requiresConfirmation()
                ->action(function ($records, $livewire) {
                    foreach ($records as $record) {
                        // Check pivot amount first
                        $currentAmount = $record->pivot?->amount;

                        // Only update if amount is empty or zero
                        if (empty($currentAmount) || $currentAmount == 0) {
                            $record->feeCollections()
                                ->updateExistingPivot(
                                    $livewire->getOwnerRecord()->getKey(),
                                    [
                                        'amount' => $livewire->getOwnerRecord()->amount,
                                        'status' => FeeCollectionStatus::PAID->value,
                                    ]
                                );
                        }
                    }
                })
                ->modalFooterActionsAlignment(Alignment::Center)
                ->deselectRecordsAfterCompletion()
                ->successNotificationTitle('Marked as paid')
                ->visible($ownerRecord->amount > 0 ? true : false);
    }

    public static function bulkMarkUnpaidAction(FeeCollection $ownerRecord)
    {
        return
            BulkAction::make('markUnpaid')
                ->label('Mark as Unpaid')
                ->icon(FeeCollectionStatus::UNPAID->getIcon())
                ->color(FeeCollectionStatus::UNPAID->getColor())
                ->requiresConfirmation()
                ->action(function ($records, $livewire) {
                    foreach ($records as $record) {
                        $record->feeCollections()
                            ->updateExistingPivot(
                                $livewire->getOwnerRecord()->getKey(),
                                [
                                    'amount' => null,
                                    'status' => FeeCollectionStatus::UNPAID->value,
                                ]
                            );
                    }
                })
                ->modalFooterActionsAlignment(Alignment::Center)
                ->deselectRecordsAfterCompletion()
                ->successNotificationTitle('Marked as unpaid')
                ->visible($ownerRecord->amount > 0 ? true : false);
    }
}
