<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances;

use BackedEnum;
use App\Models\Attendance;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = SchoolClassResource::class;

    protected static ?string $recordTitleAttribute = 'date';

    public static function getPages(): array
    {
        return [
            //
        ];
    }
}
