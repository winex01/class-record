<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Models\FeeCollection;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class TakeFeeCollectionRelationFilters
{
    public static function getTabs(FeeCollection $ownerRecord)
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(fn () => $ownerRecord->students()->count())
                ->badgeColor('success'),
        ];

        if ((float) $ownerRecord->amount === 0.0) {
            $tabs['Paid'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('amount', '>', 0))
                ->badgeColor('info')
                ->badge(fn () =>
                    $ownerRecord->students()->where('amount', '>', 0)->count()
                );

            $tabs['Unpaid'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn ($q) =>
                    $q->whereNull('amount')->orWhere('amount', '<=', 0)
                ))
                ->badgeColor('danger')
                ->badge(fn () =>
                    $ownerRecord->students()->where(fn ($q) =>
                        $q->whereNull('amount')->orWhere('amount', '<=', 0)
                    )->count()
                );
        } else {
            $tabs['Paid'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('amount', '>=', $ownerRecord->amount))
                ->badgeColor('info')
                ->badge(fn () =>
                    $ownerRecord->students()->where('amount', '>=', $ownerRecord->amount)->count()
                );

            $tabs['Partial'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('amount', '>', 0)
                    ->where('amount', '<', $ownerRecord->amount)
                )
                ->badgeColor('warning')
                ->badge(fn () =>
                    $ownerRecord->students()
                        ->where('amount', '>', 0)
                        ->where('amount', '<', $ownerRecord->amount)
                        ->count()
                );

            $tabs['Unpaid'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn ($q) =>
                    $q->whereNull('amount')->orWhere('amount', '<=', 0)
                ))
                ->badgeColor('danger')
                ->badge(fn () =>
                    $ownerRecord->students()->where(fn ($q) =>
                        $q->whereNull('amount')->orWhere('amount', '<=', 0)
                    )->count()
                );
        }

        return $tabs;
    }
}
