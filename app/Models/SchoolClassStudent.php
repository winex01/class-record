<?php

namespace App\Models;

use App\Events\SchoolClassStudentsChanged;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SchoolClassStudent extends Pivot
{
    protected static function booted()
    {
        static::created(function ($pivot) {
            event(new SchoolClassStudentsChanged(
                $pivot->schoolClass,
                [$pivot->student_id],
                'attach'
            ));
        });

        static::deleted(function ($pivot) {
            event(new SchoolClassStudentsChanged(
                $pivot->schoolClass,
                [$pivot->student_id],
                'detach'
            ));
        });
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
