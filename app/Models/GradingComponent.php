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
}
