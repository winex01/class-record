<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Models\FeeCollection;
use App\Enums\FeeCollectionStatus;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class TakeFeeCollectionRelationFilters
{
    public static function getTabs(FeeCollection $ownerRecord)
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(fn () =>
                    $ownerRecord->students()->count()
                ),
        ];

        if ((float) $ownerRecord->amount === 0.0) { // open contribution
            $tabs[FeeCollectionStatus::PAID->getLabel()] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('amount', '>', 0))
                ->badgeColor(FeeCollectionStatus::PAID->getColor())
                ->badge(fn () =>
                    $ownerRecord->students()->where('amount', '>', 0)->count()
                );

            $tabs[FeeCollectionStatus::UNPAID->getLabel()] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($query) {
                    $query->where('amount', 0)->orWhereNull('amount');
                }))
                ->badgeColor(FeeCollectionStatus::UNPAID->getColor())
                ->badge(fn () =>
                    $ownerRecord->students()->where(function ($query) {
                        $query->where('amount', 0)->orWhereNull('amount');
                    })->count()
                );
        } else {
            foreach (FeeCollectionStatus::cases() as $tab) {
                $tabs[$tab->getLabel()] = Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $tab->value))
                    ->badgeColor($tab->getColor())
                    ->badge(fn () =>
                        $ownerRecord->students()->where('status', $tab->value)->count()
                    );
            }
        }

        return $tabs;
    }
}
