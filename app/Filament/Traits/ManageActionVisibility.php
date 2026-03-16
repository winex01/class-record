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
            'submit',
            'cancel',

            'clearAll',
            'settingsAction',
            'clearAction',

            'overview',
            'finalGrades',

            // export downloads
            'export',
            'download_csv',
            'download_xlsx',

            // manage students
            'attachSelectedStudents',

            // manage attendances
            'presentStudents',
            'absentStudents',

            // manage lessons
            'downloadFiles',
            'allAttachedFiles',

            // manage assessments
            'assessmentLists',

            // manage fee collections
            'updateAmountPaid',

            // manage grades
            'grades',
            'gradingSettingsAction',
        ];

        Action::configureUsing(function (Action $action) use ($excludedActions) {
            // \Log::info('Action: ' . $action->getName() . ' | Class: ' . get_class($action));

            if ($action instanceof ViewAction || in_array($action->getName(), $excludedActions)) {
                return;
            }

            // debug($action->getName(), get_class($action));

            $action->visible(function () {
                $owner = method_exists($this, 'getOwnerRecord')
                    ? $this->getOwnerRecord()
                    : SchoolClass::find($this->schoolClassId);

                return (bool) $owner?->active;
            });
        });

    }
}
