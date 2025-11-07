<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeGradingComponent extends Model
{
    protected $table = 'grade_grading_component';
    protected $guarded = [];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class);
    }

    public function assessments()
    {
        return $this->belongsToMany(
            Assessment::class,
            'grade_component_assessments',
            'ggc_id',
            'assessment_id'
        )->withTimestamps();
    }
}
