<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Groups\Schemas\GroupForm;
use App\Filament\Resources\Students\Filters\StudentFilters;

class RecordScoreRelationFilters
{
    public static function getTabs($ownerRecord)
    {
        $tabs['All'] = Tab::make()
            ->badge(fn () => $ownerRecord->students()->count());

        $tabs['Scored'] = Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('score'))
            ->badge(fn () => $ownerRecord->students()->whereNotNull('score')->count())
            ->badgeColor('info');

        $tabs['Not Scored'] = Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('score'))
            ->badge(fn () => $ownerRecord->students()->whereNull('score')->count())
            ->badgeColor('danger');

        if ($ownerRecord->can_group_students) {
            $tabs['Grouped'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('group', '-'))
                ->badge(fn () => $ownerRecord->students()->whereNot('group', '-')->count())
                ->badgeColor('success');

            $tabs['Ungrouped'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', '-'))
                ->badge(fn () => $ownerRecord->students()->where('group', '-')->count())
                ->badgeColor('warning');
        }

        return $tabs;
    }

    public static function filters($ownerRecord)
    {
        return [
            SelectFilter::make('group')
                ->searchable()
                ->multiple()
                ->options(GroupForm::selectOptions())
                ->query(function (Builder $query, array $data) {
                    if (filled($data['values'])) {
                        $query->whereIn('assessment_student.group', $data['values']);
                    }
                })
                ->visible($ownerRecord->can_group_students),

            StudentFilters::gender()
        ];
    }
}
