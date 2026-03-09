<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Models\Attendance;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class TakeAttendanceRelationFilters
{
    public static function getTabs(Attendance $ownerRecord)
    {
        return [
            'all' => Tab::make()
                ->badge(fn () =>
                    $ownerRecord->students()->count()
                ),

            'present' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('present', true))
                ->badgeColor('success')
                ->badge(fn () =>
                    $ownerRecord->students()->where('present', true)->count()
                ),

            'absent' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('present', false))
                ->badgeColor('danger')
                ->badge(fn () =>
                    $ownerRecord->students()->where('present', false)->count()
                )
        ];
    }
}
