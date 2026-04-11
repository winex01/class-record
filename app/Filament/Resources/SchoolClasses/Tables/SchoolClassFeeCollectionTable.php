<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use App\Filament\Columns\AmountColumn;
use App\Filament\Columns\BooleanIconColumn;

class SchoolClassFeeCollectionTable
{
    public static function getColumns()
    {
        return [
            TextColumn::make('name'),

            AmountColumn::make('amount')
                ->sortable()
                ->searchable()
                ->color(fn($record) => $record->is_voluntary ? 'gray' : 'info')
                ->prefix(fn($record) => $record->is_voluntary ? false : '₱')
                ->state(fn($record) => $record->is_voluntary ? 'Voluntary' : $record->amount),

            DateColumn::make('date'),

            TextColumn::make('description')
                ->toggleable(isToggledHiddenByDefault: true),

            'total' =>
                AmountColumn::make('total')
                    ->color('primary')
                    ->tooltip('Total Collected')
                    ->sortable(),

            'status' =>
                BooleanIconColumn::make('has_unpaid')
                    ->label('Status')
                    ->state(fn($record) => !$record->has_unpaid)
                    ->tooltip(
                        fn($record) => !$record->has_unpaid
                        ? CompletedPendingStatus::COMPLETED->getLabel()
                        : CompletedPendingStatus::PENDING->getLabel()
                    )
                    ->sortable(),
        ];
    }
}
