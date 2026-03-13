<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\GradingComponent;
use Illuminate\Support\Collection;

class GradeComputationService
{
    protected Grade $grade;
    protected Collection $assessmentsByComponent;
    protected array $componentSummary;
    protected Collection $transmutations;

    public function __construct(Grade $grade, array $studentIds = [])
    {
        $grade->load([
            'schoolClass.gradeTransmutations',
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
        ]);

        $this->grade                  = $grade;
        $this->transmutations         = collect($grade->schoolClass->gradeTransmutations);
        $this->assessmentsByComponent = $this->resolveAssessmentsByComponent();
        $this->componentSummary       = $this->resolveComponentSummary();
    }

    // ── Getters ──────────────────────────────────────────────────────────────

    public function assessmentsByComponent(): Collection
    {
        return $this->assessmentsByComponent;
    }

    public function componentSummary(): array
    {
        return $this->componentSummary;
    }

    // ── Computations ─────────────────────────────────────────────────────────

    public function totalScore(Collection $assessments, int $studentId): int|float
    {
        return $assessments->sum(fn($assessment) => $assessment->getScore($studentId) ?? 0);
    }

    public function percentageScore(int|float $totalScore, int|float $maxTotalScore): float
    {
        if ($maxTotalScore == 0) return 0;

        return round(($totalScore / $maxTotalScore) * 100, 2);
    }

    public function weightedScore(float $percentageScore, int|float $componentWeightedScore): float
    {
        return round($percentageScore * ($componentWeightedScore / 100), 2);
    }

    public function initialGrade(int $studentId): float
    {
        $weightedScores = [];

        foreach ($this->assessmentsByComponent as $gradingComponentId => $assessments) {
            $TS = $this->totalScore($assessments, $studentId);
            $PS = $this->percentageScore($TS, $this->componentSummary[$gradingComponentId]['total_score']);
            $WS = $this->weightedScore($PS, $this->componentSummary[$gradingComponentId]['weighted_score']);

            $weightedScores[] = $WS;
        }

        return round(array_sum($weightedScores), 2);
    }

    public function transmutedGrade(float $initialGrade): float|string|null
    {
        if ($this->transmutations->isEmpty()) {
            return null;
        }

        $match = $this->transmutations
            ->filter(fn($t) => $initialGrade >= $t->initial_min && $initialGrade <= $t->initial_max)
            ->first();

        return $match?->transmuted_grade ?? $initialGrade;
    }

    public function gradingPeriodGrade(int $studentId): float|string
    {
        $initial = $this->initialGrade($studentId);

        return $this->transmutedGrade($initial) ?? $initial;
    }

    // ── Internal Resolvers ───────────────────────────────────────────────────

    protected function resolveAssessmentsByComponent(): Collection
    {
        return $this->grade->gradeGradingComponents
            ->groupBy(fn($ggc) => $ggc->gradingComponent->id)
            ->map(fn($group) => $group->flatMap->assessments);
    }

    protected function resolveComponentSummary(): array
    {
        $gradingComponents = GradingComponent::findMany(
            $this->assessmentsByComponent->keys()->toArray()
        )->keyBy('id');

        $summary = [];

        foreach ($this->assessmentsByComponent as $gradingComponentId => $assessments) {
            $gradingComponent = $gradingComponents->get($gradingComponentId);

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
