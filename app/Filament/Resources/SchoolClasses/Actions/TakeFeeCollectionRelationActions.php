<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Models\FeeCollection;
use Filament\Actions\BulkAction;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use App\Filament\Fields\NumericInput;
use Filament\Support\Enums\Alignment;

class TakeFeeCollectionRelationActions
{
    public static function bulkMarkPaidAction(FeeCollection $ownerRecord)
    {
        $action = BulkAction::make('markPaid')
                ->label('Mark as Paid')
                ->icon(Heroicon::CheckCircle)
                ->color('primary')
                ->deselectRecordsAfterCompletion()
                ->successNotificationTitle('Marked as paid');

        if ($ownerRecord->is_open_contribution) {
            return
                $action
                ->modalWidth(Width::Small)
                ->modalFooterActionsAlignment(Alignment::Right)
                ->schema([
                    NumericInput::make('amount')
                        ->label('Amount')
                        ->minValue(0)
                        ->required()
                        ->prefix('₱'),
                ])
                ->action(function ($records, array $data) use ($ownerRecord) {
                    foreach ($records as $record) {
                        $record->feeCollections()
                            ->updateExistingPivot(
                                $ownerRecord->getKey(),
                                ['amount' => $data['amount']]
                            );
                    }
                });
        }

        return
            $action
            ->requiresConfirmation()
            ->modalFooterActionsAlignment(Alignment::Center)
            ->action(function ($records) use ($ownerRecord) {
                foreach ($records as $record) {
                    $record->feeCollections()
                        ->updateExistingPivot(
                            $ownerRecord->getKey(),
                            ['amount' => $ownerRecord->amount]
                        );
                }
            });
    }

    public static function bulkMarkUnpaidAction(FeeCollection $ownerRecord)
    {
        return BulkAction::make('markUnpaid')
            ->label('Mark as Unpaid')
            ->icon(Heroicon::XCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->action(function ($records) use ($ownerRecord) {
                foreach ($records as $record) {
                    $record->feeCollections()
                        ->updateExistingPivot(
                            $ownerRecord->getKey(),
                            ['amount' => null]
                        );
                }
            })
            ->modalFooterActionsAlignment(Alignment::Center)
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Marked as unpaid');
    }
}
