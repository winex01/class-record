<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\View;
use Illuminate\Support\Facades\Blade;

class SchoolClassLessonActions
{
    public static function allAttachedFilesAction(SchoolClass $ownerRecord)
    {
        return
            Action::make('allAttachedFiles')
            ->label('All Attached Files')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->modalHeading('All Attached Files')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(fn () => new HtmlString(
                Blade::render(
                    '@livewire("lessons-attached-files", ["schoolClassId" => $schoolClassId])',
                    ['schoolClassId' => $ownerRecord->id]
                )
            ));
    }

    public static function downloadFilesAction()
    {
        return
            Action::make('downloadFiles')
            ->label('Download Files')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->modalHeading(fn ($record) => $record->title)
            ->modalWidth(Width::Medium)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->form([
                View::make('filament.components.download-files')
                    ->viewData(function ($record) {
                        return ['myFiles' => $record->myFiles];
                    }),
            ])
            ->visible(fn ($record) => $record->myFiles->isNotEmpty());
    }
}
