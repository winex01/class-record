<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;

class TakeAttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),

            'present' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('present', true))
                ->badgeColor('primary')
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('present', true)->count()
                ),

            'absent' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('present', false))
                ->badgeColor('danger')
                ->badge(fn () =>
                    $this->getOwnerRecord()->{static::$relationship}()->where('present', false)->count()
                )
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                ...ManageSchoolClassStudents::getColumns(),
                ToggleColumn::make('present')
                    ->offColor('danger')
            ])
            ->filters([
                ...StudentResource::getFilters()
            ])
            ->headerActions([
                ManageSchoolClassStudents::attachAction(),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                ManageSchoolClassStudents::detachBulkAction(),
            ]);
    }
}
