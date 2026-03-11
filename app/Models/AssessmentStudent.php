<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AssessmentStudent extends Pivot
{
    protected static function booted(): void
    {
        static::updated(function (AssessmentStudent $pivot) {
            if ($pivot->wasChanged('score')) {
                // fire your event here
                // event(new YourEvent($pivot));
            }
        });
    }
}
