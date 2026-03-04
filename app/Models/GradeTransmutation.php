<?php

namespace App\Models;

use Illuminate\Support\Collection;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class GradeTransmutation extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    public static function transmute(Collection $transmutations, float $rawGrade): float
    {
        $match = $transmutations->first(
            fn($t) => $rawGrade >= $t->initial_min && $rawGrade <= $t->initial_max
        );

        return $match?->transmuted_grade ?? $rawGrade;
    }
}
