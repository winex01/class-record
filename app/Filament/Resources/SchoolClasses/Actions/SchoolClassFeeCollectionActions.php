<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\Students\StudentResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\TakeFeeCollectionRelationManager;

class SchoolClassFeeCollectionActions
{
    public static function takeFeeAction()
    {
        return
            RelationManagerAction::make('takeFeeCollectionRelationManager')
            ->label('Fee')
            ->icon(StudentResource::getNavigationIcon())
            ->color('info')
            ->slideOver()
            ->relationManager(TakeFeeCollectionRelationManager::make())
            ->modalDescription(fn ($record) => new HtmlString(
                view('filament.components.fee-collection-modal-heading', [
                    'record' => $record,
                ])->render()
            ));
    }

    public static function overviewAction()
    {
        return
            Action::make('overview')
            ->color('info')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Student Fee Collection Overview')
            ->modalDescription(fn ($livewire) => 'Overview of students across all fee collections records for ' . $livewire->getOwnerRecord()->name)
            ->modalContent(fn ($livewire) => new HtmlString(
                Blade::render(
                    '@livewire("fee-collection-overview", ["schoolClassId" => $schoolClassId])',
                    ['schoolClassId' => $livewire->getOwnerRecord()->id]
                )
            ));
    }
}
