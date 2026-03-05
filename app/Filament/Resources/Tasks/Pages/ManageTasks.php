<?php

namespace App\Filament\Resources\Tasks\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\Tasks\TaskResource;
use App\Filament\Traits\CalendarEventTabFilter;

class ManageTasks extends ManageRecords
{
    use CalendarEventTabFilter;

    protected static string $resource = TaskResource::class;
}
