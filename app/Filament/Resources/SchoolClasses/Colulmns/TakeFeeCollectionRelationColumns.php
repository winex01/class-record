<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Models\FeeCollection;
use App\Filament\Columns\TextInputColumn;

class TakeFeeCollectionRelationColumns
{
    public static function schema(FeeCollection $ownerRecord)
    {
        return [
            ...SchoolClassStudentColumns::schema(),

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
                ->afterStateUpdated(fn ($livewire) => $livewire->dispatch('refreshCollapsibleTableWidget'))
                ->disabled(fn () => !$ownerRecord->schoolClass->active),
        ];
    }
}
