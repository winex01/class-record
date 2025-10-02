<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Carbon;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

trait CalendarEventTabs
{
    public function getTabs(): array
    {
        $now = Carbon::now();

        return [
            Tab::make('All')
                ->badge(fn () => $this->getTableQuery()->count()),

            Tab::make('Past')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ends_at', '<', $now))
                ->badge(fn () => $this->getTableQuery()->where('ends_at', '<', $now)->count()),

            Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                )
                ->badge(fn () => $this->getTableQuery()
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->count()
                ),

            Tab::make('Upcoming')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('starts_at', '>', $now))
                ->badge(fn () => $this->getTableQuery()->where('starts_at', '>', $now)->count()),
        ];
    }
}
