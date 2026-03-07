<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Groups\Forms\GroupForm;
use App\Filament\Resources\Students\Filters\StudentFilters;

class RecordScoreRelationFilters
{
    public static function getTabs($ownerRecord)
    {
        $tabs['all'] = Tab::make()
            ->badge(fn () =>
                $ownerRecord->students()->count()
            );

        if ($ownerRecord->can_group_students) {
            $tabs['With Group'] = Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('group', '-'))
                    ->badgeColor('info')
                    ->badge(fn () =>
                        $ownerRecord->students()->whereNot('group', '-')->count()
            );

            $tabs['No Group'] = Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('group', '-'))
                    ->badgeColor('danger')
                    ->badge(fn () =>
                        $ownerRecord->students()->where('group', '-')->count()
            );
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
