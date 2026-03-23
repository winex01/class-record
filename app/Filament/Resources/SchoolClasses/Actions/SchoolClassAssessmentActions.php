<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\Students\StudentResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\RecordScoreRelationManager;

class SchoolClassAssessmentActions
{
    public static function overviewAction()
    {
        return
            Action::make('overview')
            ->color('info')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Student Assessment Overview')
            ->modalDescription(function ($livewire) {
                return 'Overview of students across all assessment records for ' . $livewire->getOwnerRecord()->name;
            })
            ->modalContent(fn ($livewire) => new HtmlString(
                Blade::render(
                    '@livewire("assessment-overview", ["schoolClassId" => $schoolClassId])',
                    ['schoolClassId' => $livewire->getOwnerRecord()->id]
                )
            ));
    }

    public static function recordScoreAction()
    {
        return
            RelationManagerAction::make('recordScoreRelationManager')
            ->label('Score')
            ->icon(StudentResource::getNavigationIcon())
            ->color('info')
            ->slideOver()
            ->compact()
            ->relationManager(RecordScoreRelationManager::make())
            ->modalDescription(fn ($record) => new HtmlString(
                view('filament.components.assessment-modal-heading', [
                    'record' => $record,
                ])->render()
            ));
    }
}
