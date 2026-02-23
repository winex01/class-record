<?php

namespace App\Filament\Traits;

use Filament\Support\Enums\Width;
use App\Filament\Widgets\SubjectDetailsWidget;

trait ManageSchoolClassInitTrait
{
    protected function getHeaderWidgets(): array
    {
        return [
            SubjectDetailsWidget::make([
                'record' => $this->getOwnerRecord(),
            ]),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
