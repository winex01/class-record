<?php

namespace App\Filament\Resources\Notes\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Notes\NoteResource;
use App\Filament\Traits\CalendarEventTabFilter;

class ManageNotes extends ManageRecords
{
    use CalendarEventTabFilter;

    protected static string $resource = NoteResource::class;
}
