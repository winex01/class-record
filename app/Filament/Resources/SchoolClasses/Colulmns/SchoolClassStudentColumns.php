<?php

namespace App\Filament\Resources\SchoolClasses\Colulmns;

use App\Filament\Resources\Students\Tables\StudentsTable;


class SchoolClassStudentColumns
{
    public static function schema($defaultShownColumns = ['photo', 'full_name', 'gender'])
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
