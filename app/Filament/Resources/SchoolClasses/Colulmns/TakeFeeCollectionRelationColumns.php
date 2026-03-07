<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Models\FeeCollection;
use App\Enums\FeeCollectionStatus;
use App\Filament\Columns\SelectColumn;
use App\Filament\Columns\TextInputColumn;

class TakeFeeCollectionRelationColumns
{
    public static function schema(FeeCollection $ownerRecord)
    {
        return [
            ...SchoolClassStudentColumns::schema(),

            SelectColumn::make('status')
                ->options(FeeCollectionStatus::class)
                ->afterStateUpdated(function ($state, $record) use ($ownerRecord) {
                    if ($state === FeeCollectionStatus::PAID->value) {
                        // check pivot amount first
                        $currentAmount = $record->pivot?->amount;

                        if (empty($currentAmount) || $currentAmount == 0) {
                            $record->feeCollections()
                                ->updateExistingPivot(
                                    $ownerRecord->getKey(),
                                    ['amount' => $ownerRecord->amount]
                                );
                        }
                    } elseif ($state === FeeCollectionStatus::UNPAID->value) {
                        $record->feeCollections()
                            ->updateExistingPivot(
                                $ownerRecord->getKey(),
                                ['amount' => null]
                            );
                    }
                })
                ->visible($ownerRecord->amount > 0 ? true : false)
                ->disabled(fn () => !$ownerRecord->schoolClass->active),

            TextInputColumn::make('amount')
                ->rules(function () use ($ownerRecord) {
                    $amount = $ownerRecord->amount ?? 0;

                    return $amount > 0
                        ? ['numeric', 'min:0', 'max:' . $amount]
                        : ['numeric', 'min:0', 'max:99999999'];
                })
                ->placeholder(function () use ($ownerRecord) {
                    if (!$ownerRecord->schoolClass->active) {
                        return null;
                    }

                    return $ownerRecord->amount == 0
                        ? '₱'
                        : 'Fee ₱' . ($ownerRecord->amount ?? 0);
                })
                ->disabled(fn () => !$ownerRecord->schoolClass->active),
        ];
    }
}
