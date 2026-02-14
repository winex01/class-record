<?php

namespace App\Filament\Resources\SchoolClasses\RelationManagers;

use App\Services\Column;
use Filament\Tables\Table;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Groups\GroupResource;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class RecordScoreRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        $tabs['all'] = Tab::make()
            ->badge(fn () =>
                $this->getOwnerRecord()->{static::$relationship}()->count()
            );

        if ($this->getOwnerRecord()->can_group_students) {
            $tabs['With Group'] = Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('group', '-'))
                    ->badgeColor('info')
                    ->badge(fn () =>
                        $this->getOwnerRecord()->{static::$relationship}()->whereNot('group', '-')->count()
            );

            $tabs['No Group'] = Tab::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('group', '-'))
                    ->badgeColor('danger')
                    ->badge(fn () =>
                        $this->getOwnerRecord()->{static::$relationship}()->where('group', '-')->count()
            );
        }

        return $tabs;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->defaultSort(StudentResource::defaultNameSort('asc'))
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),

                Column::select('group')
                    ->options(function ($record) {
                        $baseOptions = GroupResource::selectOptions();

                        // Get current value and add it if it doesn't exist
                        $currentValue = $record->pivot->group ?? null;
                            if ($currentValue && !array_key_exists($currentValue, $baseOptions)) {
                                $baseOptions[$currentValue] = $currentValue;
                            }

                        return $baseOptions;
                    })
                    ->afterStateUpdated(function ($state, $record) {
                        // If the state is null or empty, set it to '-'
                        if (empty($state)) {
                            $record->pivot->group = '-';
                            $record->pivot->save();
                        }
                    })
                    ->visible($this->getOwnerRecord()->can_group_students),

                Column::textInput('score')
                    ->placeholder('Max: ' . ($this->getOwnerRecord()->max_score ?? 0))
                    ->rules(['numeric', 'min:0', 'max:' . ($this->getOwnerRecord()->max_score ?? 0)])
            ])
            ->filters([
                SelectFilter::make('group')
                    ->searchable()
                    ->multiple()
                    ->options(GroupResource::selectOptions())
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['values'])) {
                            $query->whereIn('assessment_student.group', $data['values']);
                        }
                    })
                    ->visible($this->getOwnerRecord()->can_group_students),

                    ...StudentResource::getFilters(),
            ])
            ->filtersFormWidth(Width::Medium)
            ->headerActions([
                ManageSchoolClassStudents::attachAction($this->getOwnerRecord()),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                ManageSchoolClassStudents::detachBulkAction(),
            ])
            ->groups(
                $this->getOwnerRecord()->can_group_students
                    ? [\Filament\Tables\Grouping\Group::make('group')]
                    : []
            )
            ->defaultGroup(
                $this->getOwnerRecord()->can_group_students ? 'group' : null
            );
    }
}
