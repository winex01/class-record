<?php

namespace App\Models;

use App\Models\GradingComponent;
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
     * Get assessments grouped by grading component ID for a given grade.
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
            ->groupBy(fn($ggc) => $ggc->gradingComponent->id)
            ->map(fn($group) => $group->flatMap->assessments);
    }

    /**
     * Get a summary of each grading component (total score, weights, label)
     * keyed by grading_component_id.
     */
    public static function componentSummary(int $gradeId, Collection $assessmentsByComponent): array
    {
        $summary = [];

        foreach ($assessmentsByComponent as $gradingComponentId => $assessments) {
            $gradingComponent = GradingComponent::find($gradingComponentId);

            $summary[$gradingComponentId] = [
                'total_score'          => $assessments->sum('max_score'),
                'weighted_score'       => $gradingComponent->weighted_score,
                'weighted_score_label' => $gradingComponent->weighted_score_percentage_label,
                'component_label'      => $gradingComponent->name,
            ];
        }

        return $summary;
    }
}
