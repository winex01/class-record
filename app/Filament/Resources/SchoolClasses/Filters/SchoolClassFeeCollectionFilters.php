<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Models\SchoolClass;
use App\Enums\FeeCollectionStatus;
use App\Enums\CompletedPendingStatus;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassFeeCollectionFilters
{
    public static function getTabs(SchoolClass $ownerRecord)
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $ownerRecord->feeCollections()->count()
                ),

            CompletedPendingStatus::COMPLETED->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDoesntHave('students', function ($q) {
                        $q->where('status', '!=', FeeCollectionStatus::PAID->value);
                    })
                )
                ->badgeColor('info')
                ->badge(fn () =>
                    $ownerRecord
                        ->feeCollections()
                        ->whereDoesntHave('students', function ($q) {
                            $q->where('status', '!=', FeeCollectionStatus::PAID->value);
                        })
                        ->count()
                ),

            CompletedPendingStatus::PENDING->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('students', function ($q) {
                        $q->where('status', '!=', FeeCollectionStatus::PAID->value);
                    })
                )
                ->badgeColor('danger')
                ->badge(fn () =>
                    $ownerRecord
                        ->feeCollections()
                        ->whereHas('students', function ($q) {
                            $q->where('status', '!=', FeeCollectionStatus::PAID->value);
                        })
                        ->count()
                ),

        ];
    }
}
