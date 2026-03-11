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
                ->modifyQueryUsing(fn (Builder $query) => $query->withPaymentStatus(true))
                ->badgeColor('success')
                ->badge(fn () => $ownerRecord->feeCollections()->withPaymentStatus(true)->count()),

            CompletedPendingStatus::PENDING->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->withPaymentStatus(false))
                ->badgeColor('danger')
                ->badge(fn () => $ownerRecord->feeCollections()->withPaymentStatus(false)->count()),
        ];
    }
}
