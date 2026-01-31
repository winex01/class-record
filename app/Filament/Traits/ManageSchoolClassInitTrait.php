<?php

namespace App\Filament\Traits;

use Filament\Support\Enums\Width;

trait ManageSchoolClassInitTrait
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SubjectDetailsWidget::make([
                'record' => $this->getOwnerRecord(),
            ]),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
