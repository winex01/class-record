<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class ManageSchoolClasses extends ManageRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Large),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () =>
                    static::getModel()::count()
                ),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true))
                ->badge(fn () => static::getModel()::where('active', true)->count())
                ->badgeColor('success'),

            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false))
                ->badge(fn () => static::getModel()::where('active', false)->count())
                ->badgeColor('warning'),
        ];
    }
}
