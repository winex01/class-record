<?php

namespace App\Filament\Traits;

use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;

trait ManageActionVisibility
{
    public function booted(): void
    {
        $excludedActions = [
            // filament
            'openFilters',
            'openColumnManager',

            'overview',

            // manage attendances
            'presentStudents',
            'absentStudents',

            // manage lessons
            'downloadFiles',
            'allAttachedFiles',

            // manage assessments
            'assessmentLists',
        ];

        Action::configureUsing(function (Action $action) use ($excludedActions) {
            if ($action instanceof ViewAction || in_array($action->getName(), $excludedActions)) {
                return;
            }

            $action->visible(function () {
                $owner = method_exists($this, 'getOwnerRecord')
                    ? $this->getOwnerRecord()
                    : SchoolClass::find($this->schoolClassId);

                return (bool) $owner?->active;
            });
        });
    }
}
