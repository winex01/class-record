<?php

namespace App\Filament\Resources\Notes\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Concerns\CalendarEventTabs;
use App\Filament\Resources\Notes\NoteResource;

class ManageNotes extends ManageRecords
{
    use CalendarEventTabs;

    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::Medium)
        ];
    }
}
