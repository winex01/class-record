<?php

namespace App\Filament\Resources\SchoolClasses\Actions;

use App\Models\SchoolClass;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachBulkAction;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;

class SchoolClassStudentActions
{
    public static function attachAction($ownerRecord = null)
    {
        $attachAction = AttachAction::make()
            ->label('Attach Students')
            ->color('info')
            ->multiple()
            ->preloadRecordSelect()
            ->closeModalByClickingAway(false)
            ->recordSelectSearchColumns([
                'last_name',
                'first_name',
                'middle_name',
                'suffix_name',
            ]);

        if ($ownerRecord !== null && !$ownerRecord instanceof SchoolClass) {
            $attachAction
                ->modalDescription('Only students who are part of this class are available for selection.')
                ->recordSelectOptionsQuery(function ($query) use ($ownerRecord) {
                    return $query->whereIn('students.id', SchoolClassResource::getStudents($ownerRecord->school_class_id));
                });
        }

        return $attachAction;
    }

    public static function detachBulkAction()
    {
        return
            DetachBulkAction::make()
            ->color('warning')
            ->action(function ($records, $livewire) {
                foreach ($records as $record) {
                    $livewire->getRelationship()->detach($record);
                }
            });
    }
}
