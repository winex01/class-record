<?php

namespace App\Models\Concerns;

use App\Models\SchoolClass;

trait BelongsToSchoolClass
{
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
