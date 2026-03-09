<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Models\SchoolClass;
use App\Enums\CompletedPendingStatus;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassFeeCollectionFilters
{
    public static function getTabs(SchoolClass $ownerRecord)
    {
        return [
            'all' => Tab::make()
                ->badge(fn () => $ownerRecord->feeCollections()->count()),

            CompletedPendingStatus::COMPLETED->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDoesntHave('students', function ($q) {
                        $q->where(function ($sub) {
                            // Open contribution: has null pivot amount
                            $sub->whereRaw('fee_collections.amount = 0')
                                ->whereNull('fee_collection_student.amount');
                        })->orWhere(function ($sub) {
                            // Fixed amount: underpaid or unpaid
                            $sub->whereRaw('fee_collections.amount > 0')
                                ->whereRaw('COALESCE(fee_collection_student.amount, 0) < fee_collections.amount');
                        });
                    })
                )
                ->badgeColor('success')
                ->badge(fn () =>
                    $ownerRecord->feeCollections()
                        ->whereDoesntHave('students', function ($q) {
                            $q->where(function ($sub) {
                                $sub->whereRaw('fee_collections.amount = 0')
                                    ->whereNull('fee_collection_student.amount');
                            })->orWhere(function ($sub) {
                                $sub->whereRaw('fee_collections.amount > 0')
                                    ->whereRaw('COALESCE(fee_collection_student.amount, 0) < fee_collections.amount');
                            });
                        })
                        ->count()
                ),

            CompletedPendingStatus::PENDING->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('students', function ($q) {
                        $q->where(function ($sub) {
                            // Open contribution: has null pivot amount
                            $sub->whereRaw('fee_collections.amount = 0')
                                ->whereNull('fee_collection_student.amount');
                        })->orWhere(function ($sub) {
                            // Fixed amount: underpaid or unpaid
                            $sub->whereRaw('fee_collections.amount > 0')
                                ->whereRaw('COALESCE(fee_collection_student.amount, 0) < fee_collections.amount');
                        });
                    })
                )
                ->badgeColor('danger')
                ->badge(fn () =>
                    $ownerRecord->feeCollections()
                        ->whereHas('students', function ($q) {
                            $q->where(function ($sub) {
                                $sub->whereRaw('fee_collections.amount = 0')
                                    ->whereNull('fee_collection_student.amount');
                            })->orWhere(function ($sub) {
                                $sub->whereRaw('fee_collections.amount > 0')
                                    ->whereRaw('COALESCE(fee_collection_student.amount, 0) < fee_collections.amount');
                            });
                        })
                        ->count()
                ),
        ];
    }
}
