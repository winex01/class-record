<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class Grade extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    protected $casts = [
        'components' => 'array',
    ];

    public function getStatusAttribute()
    {
        $schoolClass = $this->schoolClass; // if relationship exists
        $gradingComponentIds = $schoolClass?->gradingComponents()->pluck('id')->toArray() ?? [];
        $components = collect($this->components ?? [])
            ->pluck('grading_component_id')
            ->filter()
            ->toArray();

        $missing = array_diff($gradingComponentIds, $components);

        return !empty($missing) ? false : true;
    }
}
