<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use App\Filament\Resources\Students\StudentResource;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use App\Filament\Resources\SchoolClasses\RelationManagers\TakeAttendanceRelationManager;

class SchoolClassAttendanceActions
{
    public static function takeAttendanceAction()
    {
        return
            RelationManagerAction::make('takeAttendanceRelationManager')
            ->label('Attendance')
            ->icon(StudentResource::getNavigationIcon())
            ->color('info')
            ->slideOver()
            ->compact()
            ->relationManager(TakeAttendanceRelationManager::make())
            ->modalHeading(fn ($record) => new HtmlString(
                view('filament.components.attendance-modal-heading', [
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
            ->modalHeading('Student Attendance Overview')
            ->modalDescription(fn ($livewire) => 'Overview of students across all attendance records for ' . $livewire->getOwnerRecord()->name)
            ->modalContent(fn ($livewire) => new HtmlString(
                Blade::render(
                    '<div class="mb-4 text-sm text-gray-600 dark:text-gray-400" style="margin-top:-1.5rem;">
                        <strong>Tip:</strong> Click on the present/absent counts to view the specific dates.
                    </div>
                    @livewire("attendance-overview", ["schoolClassId" => $schoolClassId])',
                    ['schoolClassId' => $livewire->getOwnerRecord()->id]
                )
            ));
    }
}
