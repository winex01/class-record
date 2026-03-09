<?php

namespace App\Listeners;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Events\SchoolClassStudentsChanged;

class SyncStudentsToRelatedModels
{
    public function handle(SchoolClassStudentsChanged $event): void
    {
        foreach ([
            Attendance::class,
            Assessment::class,
            FeeCollection::class,
        ] as $model) {
            $model::where('school_class_id', $event->schoolClass->id)
                ->get()
                ->each(function($a) use ($event) {
                    if ($event->action === 'attach') {
                        $a->students()->syncWithoutDetaching($event->studentIds);
                    } else {
                        $a->students()->detach($event->studentIds);
                    }
                });
        }
    }
}
