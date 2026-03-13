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

    public static function totalScore(Collection $assessments, int $studentId): int|float
    {
        return $assessments->sum(fn($assessment) => $assessment->getScore($studentId) ?? 0);
    }

    public static function percentageScore(int|float $totalScore, int|float $maxTotalScore): float
    {
        if ($maxTotalScore == 0) return 0;

        return round(($totalScore / $maxTotalScore) * 100, 2);
    }

    public static function weightedScore(float $percentageScore, int|float $componentWeightedScore): float
    {
        return round($percentageScore * ($componentWeightedScore / 100), 2);
    }

    public static function initialGrade(array $weightedScores): float
    {
        return round(array_sum($weightedScores), 2);
    }

    public static function transmutedGrade(float $initialGrade, int $gradeId): float|string|null
    {
        $grade = static::with('schoolClass.gradeTransmutations')->findOrFail($gradeId);

        $transmutations = $grade->schoolClass->gradeTransmutations;

        if ($transmutations->isEmpty()) {
            return null;
        }

        $match = $transmutations
            ->filter(fn($t) => $initialGrade >= $t->initial_min && $initialGrade <= $t->initial_max)
            ->first();

        return $match?->transmuted_grade ?? $initialGrade;
    }
}
