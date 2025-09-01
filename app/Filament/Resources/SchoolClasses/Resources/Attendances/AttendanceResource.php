<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances;

use App\Filament\Resources\SchoolClasses\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\SchoolClasses\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\SchoolClasses\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\SchoolClasses\Resources\Attendances\Tables\AttendancesTable;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = SchoolClassResource::class;

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateAttendance::route('/create'),
        ];
    }
}
