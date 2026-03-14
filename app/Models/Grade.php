<?php

namespace App\Models;

use App\Models\GradingComponent;
use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToSchoolClass;

class Grade extends Model
{
    use BelongsToUser;
    use BelongsToSchoolClass;

    protected $guarded = [];

    public function gradeGradingComponents()
    {
        return $this->hasMany(GradeGradingComponent::class)
            ->orderBy(
                GradingComponent::select('sort')
                    ->whereColumn('grading_components.id', 'grade_grading_component.grading_component_id')
            );
    }

    public function getIsCompleteAttribute(): bool
    {
        $this->loadMissing('gradeGradingComponents', 'schoolClass.gradingComponents');

        if (! $this->schoolClass) {
            return false;
        }

        return $this->gradeGradingComponents->count() === $this->schoolClass->gradingComponents()->count();
    }
}
