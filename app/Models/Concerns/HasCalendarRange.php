<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\ValueObjects\FetchInfo;

trait HasCalendarRange
{
    public function scopeWithinCalendarRange(Builder $query, FetchInfo $info): Builder
    {
        return $query
            ->whereDate('starts_at', '<=', $info->end)
            ->whereDate('ends_at', '>=', $info->start);
    }
}
