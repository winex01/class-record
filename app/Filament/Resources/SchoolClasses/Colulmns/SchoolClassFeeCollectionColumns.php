<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Filament\Columns\DateColumn;
use App\Filament\Columns\TextColumn;
use App\Enums\CompletedPendingStatus;
use App\Filament\Columns\AmountColumn;
use App\Filament\Columns\BooleanIconColumn;

class SchoolClassFeeCollectionColumns
{
    public static function schema()
    {
        return [
            TextColumn::make('name'),

            AmountColumn::make('amount')
                ->color('info')
                ->placeholder('—')
                ->sortable()
                ->searchable()
                ->getStateUsing(fn ($record) => $record->amount > 0 ? $record->amount : null),

            DateColumn::make('date'),

            TextColumn::make('description')
                ->toggleable(isToggledHiddenByDefault: true),

            'total' =>
            AmountColumn::make('total')
                ->color('primary')
                ->state(fn ($record) => $record->students()->sum('amount'))
                ->tooltip('Total Collected')
                ->sortable(
                    query: fn ($query, string $direction) =>
                        $query->withSum('students as total', 'fee_collection_student.amount')
                            ->orderBy('total', $direction)
                )
                ->searchable(
                    query: fn ($query, string $search) =>
                        $query->whereRaw(
                            '(SELECT COALESCE(SUM(fee_collection_student.amount), 0)
                            FROM fee_collection_student
                            WHERE fee_collection_student.fee_collection_id = fee_collections.id) LIKE ?',
                            ["%{$search}%"]
                        )
                ),

            'status' =>
            BooleanIconColumn::make('status')
                ->getStateUsing(function ($record) {
                    if ($record->is_open_contribution) {
                        return !$record->students()->wherePivotNull('amount')->exists();
                    }

                    $students = $record->students()->withPivot(['amount', 'status'])->get();

                    // Every student must have paid at least the required amount
                    $hasUnderpaidOrUnpaid = $students->contains(function ($student) use ($record) {
                        $paid = $student->pivot->amount ?? 0;
                        return $paid < $record->amount;
                    });

                    return !$hasUnderpaidOrUnpaid;
                })
                ->tooltip(function ($record) {
                    if ($record->is_open_contribution) {
                        $hasPending = $record->students()->wherePivotNull('amount')->exists();
                        return $hasPending
                            ? CompletedPendingStatus::PENDING->getLabel()
                            : CompletedPendingStatus::COMPLETED->getLabel();
                    }

                    $students = $record->students()->withPivot(['amount', 'status'])->get();

                    $hasUnderpaidOrUnpaid = $students->contains(function ($student) use ($record) {
                        $paid = $student->pivot->amount ?? 0;
                        return $paid < $record->amount;
                    });

                    return !$hasUnderpaidOrUnpaid
                        ? CompletedPendingStatus::COMPLETED->getLabel()
                        : CompletedPendingStatus::PENDING->getLabel();
                })
                ->sortable(
                    query: fn ($query, string $direction) => $query
                        ->withExists([
                            'students as has_unpaid' => fn ($q) => $q
                                ->where(fn ($sub) => $sub
                                    ->whereNull('fee_collection_student.amount')
                                    ->orWhere('fee_collection_student.amount', '<', \DB::raw('fee_collections.amount'))
                                )
                        ])
                        ->orderBy('has_unpaid', $direction)
                )
        ];
    }
}
