<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances\Pages;

use App\Filament\Resources\SchoolClasses\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;
}
