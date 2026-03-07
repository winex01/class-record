<?php

namespace App\Filament\Resources\SchoolClasses\Filters;

use App\Models\SchoolClass;
use App\Enums\CompletedPendingStatus;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassAssessmentFilters
{
    public static function types()
    {
        return
            SelectFilter::make('assessmentType')
            ->relationship('assessmentType', 'name')
            ->multiple()
            ->searchable()
            ->preload();
    }

    public static function getTabs(SchoolClass $ownerRecord)
    {
        $tabs = [
            'all' => Tab::make()
                ->badge(fn () =>
                    $ownerRecord->assessments()->count()
                ),

            CompletedPendingStatus::COMPLETED->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDoesntHave('students', function ($q) {
                        $q->whereNull('score');
                    })
                )
                ->badgeColor('info')
                ->badge(fn () =>
                    $ownerRecord
                        ->assessments()
                        ->whereDoesntHave('students', function ($q) {
                            $q->whereNull('score');
                        })
                        ->count()
                ),

            CompletedPendingStatus::PENDING->getLabel() => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('students', function ($q) {
                        $q->whereNull('score');
                    })
                )
                ->badgeColor('danger')
                ->badge(fn () =>
                    $ownerRecord
                        ->assessments()
                        ->whereHas('students', function ($q) {
                            $q->whereNull('score');
                        })
                        ->count()
                ),
        ];

        $tabs['unassigned'] = Tab::make()
            ->label('Unassigned')
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->whereDoesntHave('gradeGradingComponents')
            )
            ->badgeColor('warning')
            ->badge(fn () =>
                $ownerRecord
                    ->assessments()
                    ->whereDoesntHave('gradeGradingComponents')
                    ->count()
            );

        // Dynamically fetch distinct grading periods linked to this school class's assessments
        $gradingPeriods = $ownerRecord
            ->grades()
            ->whereHas('gradeGradingComponents.assessments')
            ->pluck('grading_period')
            ->unique()
            ->filter()
            ->values();

        foreach ($gradingPeriods as $period) {
            $tabs[$period] = Tab::make()
                ->label($period)
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('gradeGradingComponents.grade', function (Builder $q) use ($period) {
                        $q->where('grading_period', $period);
                    })
                )
                ->badge(fn () =>
                    $ownerRecord
                        ->assessments()
                        ->whereHas('gradeGradingComponents.grade', function ($q) use ($period) {
                            $q->where('grading_period', $period);
                        })
                        ->count()
                );
        }

        return $tabs;
    }
}
