<?php

namespace App\Filament\Resources\SchoolClasses\Resources\Attendances;

use BackedEnum;
use Filament\Pages\Page;
use App\Models\Attendance;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Filament\Resources\SchoolClasses\Pages\ManageSchoolClassStudents;
use App\Filament\Resources\SchoolClasses\Resources\Attendances\Pages\ManageAttendanceStudents;

class AttendanceResource extends Resource
{
    // TODO:: Back button

    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = SchoolClassResource::class;

    protected static ?string $recordTitleAttribute = 'date';

    public static function getPages(): array
    {
        return [
            'attendance-students' => ManageAttendanceStudents::route('/{record}/students'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $subNavs = [];
        foreach (SchoolClassResource::getRecordSubNavigation($page) as $key => $subNav) {
            $record = $page->getRecord();

            // dd($subNav->getUrl());

            if ($key == 'attendances') {
                $subNav->isActiveWhen(fn () => $page instanceof ManageAttendanceStudents);
                $subNav->url(ManageSchoolClassStudents::getUrl(['record' => $record->school_class_id]));
            }else {
                $subNav->url(ManageSchoolClassStudents::getUrl(['record' => $record]));
            }

            $subNavs[$key] = $subNav;

        }

        return $subNavs;
    }
}
