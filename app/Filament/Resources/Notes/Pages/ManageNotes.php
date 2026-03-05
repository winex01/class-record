<?php

namespace App\Filament\Resources\Notes\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Notes\NoteResource;
use App\Filament\Traits\CalendarEventTabFilter;

class ManageNotes extends ManageRecords
{
    use CalendarEventTabFilter;

    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Note')
                ->modalWidth(Width::Medium)
        ];
    }
}
