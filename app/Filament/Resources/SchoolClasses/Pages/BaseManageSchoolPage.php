<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;

class BaseManageSchoolPage extends ManageRelatedRecords
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SubjectDetailsWidget::make([
                'record' => $this->getOwnerRecord(),
            ]),
        ];
    }
}
