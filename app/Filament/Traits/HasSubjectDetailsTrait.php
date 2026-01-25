<?php

namespace App\Filament\Traits;

trait HasSubjectDetailsTrait
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
