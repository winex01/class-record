<?php

namespace App\Filament\Resources\Students\Pages;

use App\Enums\Gender;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Students\StudentResource;

class ManageStudents extends ManageRecords
{
    protected static string $resource = StudentResource::class;

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
            'All' => Tab::make()
                ->badge(fn () => $this->getTableQuery()->count()),

            'Male' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::MALE->value))
                ->badgeColor(Gender::MALE->getColor())
                ->badge(fn () => $this->getTableQuery()->where('gender', Gender::MALE->value)->count()),

            'Female' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('gender', Gender::FEMALE->value))
                ->badgeColor(Gender::FEMALE->getColor())
                ->badge(fn () => $this->getTableQuery()->where('gender', Gender::FEMALE->value)->count()),
        ];
    }
}
