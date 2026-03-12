<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class GradingComponent extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    public function gradeGradingComponents()
    {
        return $this->hasMany(GradeGradingComponent::class);
    }

    public function getLabelAttribute()
    {
        return "{$this->name} (" . (int) round(floatval($this->weighted_score)) . "%)";
    }

    public function getWeightedScorePercentageLabelAttribute()
    {
        return (int) round(floatval($this->weighted_score)) . "%";
    }

    /**
     * Static — use when you only have the ID
     * GradingComponent::getLabel(id: 107)
     */
    public static function getLabel(int $id): string
    {
        $gradingComponent = static::findOrFail($id);

        return $gradingComponent->label;
    }
}
