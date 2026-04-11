<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use App\Filament\Resources\Students\Tables\StudentsTable;


class SchoolClassStudentTable
{
    public static function getColumns($defaultShownColumns = ['photo', 'full_name', 'gender'])
    {
        $columns = StudentsTable::getColumns();

        foreach ($columns as $key => $col) {
            if (!in_array($col->getName(), $defaultShownColumns)) {
                $columns[$key] = $col->toggleable(isToggledHiddenByDefault: true);
            }
        }

        return $columns;
    }
}
