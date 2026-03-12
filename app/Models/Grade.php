<?php

namespace App\Models;

use Illuminate\Support\Collection;
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
        return $this->hasMany(GradeGradingComponent::class);
    }

    public function orderedGradeGradingComponents()
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

    /**
     * Get assessments grouped by grading component label for a given grade.
     * Optionally filter by specific student IDs.
     */
    public static function assessmentsByComponent(int $gradeId, array $studentIds = []): Collection
    {
        $grade = static::with([
            'gradeGradingComponents.gradingComponent',
            'gradeGradingComponents.assessments.students' => function ($q) use ($studentIds) {
                if (!empty($studentIds)) {
                    $q->whereIn('students.id', $studentIds);
                }
                $q->select(
                    'students.id',
                    'students.first_name',
                    'students.last_name',
                    'students.middle_name',
                    'students.suffix_name',
                    'students.gender'
                )->withPivot('score');
            }
        ])->findOrFail($gradeId);

        return $grade->gradeGradingComponents
            ->groupBy(fn($ggc) => $ggc->gradingComponent?->label)
            ->map(fn($group) => $group->flatMap->assessments);
    }
}
