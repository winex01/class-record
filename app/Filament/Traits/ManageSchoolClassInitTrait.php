<?php

namespace App\Filament\Traits;

use Filament\Support\Enums\Width;
use App\Filament\Widgets\SubjectDetailsWidget;

trait ManageSchoolClassInitTrait
{
    use ManageActionVisibility;

    protected function getListeners(): array
    {
        return [
            'refreshTable' => '$refresh',
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ...static::myWidgets($this->getOwnerRecord()),
        ];
    }

    public static function myWidgets($ownerRecord)
    {
        return [
            SubjectDetailsWidget::make([
                'record' => $ownerRecord,
            ]),
        ];
    }

    /**
     * NOTE: This method removes the extra left sidebar space on relation pages.
     * For custom pages extending Page (not ManageRelatedRecords), add this property directly:
     * protected ?string $maxContentWidth = 'full';
     * since custom pages don't use this trait.
     */
    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
