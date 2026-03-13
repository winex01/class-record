<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Enums\Gender;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassStudentFilters
{
    public static function getTabs($ownerRecord)
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () =>
                    $ownerRecord->students()->count()
                ),

            'male' => Tab::make('Male')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::MALE->value))
                ->badgeColor(Gender::MALE->getColor())
                ->badge(fn () =>
                    $ownerRecord->students()->where('gender', Gender::MALE->value)->count()
                ),

            'female' => Tab::make('Female')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::FEMALE->value))
                ->badgeColor(Gender::FEMALE->getColor())
                ->badge(fn () =>
                    $ownerRecord->students()->where('gender', Gender::FEMALE->value)->count()
                )
        ];
    }
}
