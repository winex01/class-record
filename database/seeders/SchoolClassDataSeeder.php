<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\FeeCollection;
use App\Models\AssessmentType;
use Illuminate\Database\Seeder;
use SebastianBergmann\Type\TypeName;

class SchoolClassDataSeeder extends Seeder
{
    public function run(int $classId = null): void
    {
        $resolvedId = $classId ?? $this->command->ask('Enter school class ID (leave blank to create new)');

        $class = $resolvedId
            ? SchoolClass::findOrFail((int) $resolvedId)
            : SchoolClass::factory()->create();

        $this->command->info("Generating data for class: {$class->name} (ID: {$class->id})");

        $this->generateAttendances($class);
        $this->generateAssessment($class, 1, 20, '15-30'); // Quiz
        $this->generateAssessment($class, 2, 4, '50-70');  // Exam
        $this->generateAssessment($class, 3, 4, '40-50');  // Homework
        $this->generateAssessment($class, 4, 4, '50-60');  // Project
        $this->generateAssessment($class, 5, 4, '50-80');  // Oral
        Lesson::factory()->count(5)->forUser($class->user_id)->forSchoolClass($class->id)->create();
        $this->generateFeeCollections($class);

        $this->command->info("Done!");
    }

    public function getOptions(): array
    {
        return [
            ['classId', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'The ID of the school class to seed data for'],
        ];
    }

    // --- Private Generators ---

    private function generateAttendances(SchoolClass $class, $count = 10): void
    {
        for ($i = 0; $i < $count; $i++) {
            $date = Carbon::today()->subDays($i);

            $attendance = $class->attendances()->create([
                'user_id' => $class->user_id,
                'date'    => $date,
            ]);

            $pivotData = $class->students->mapWithKeys(function ($student) {
                return [
                    $student->id => ['present' => mt_rand(1, 100) <= 80],
                ];
            });

            $attendance->students()->attach($pivotData);
        }
    }

    private function generateFeeCollections(SchoolClass $class): void
    {
        $feeCollections = collect([
            ...FeeCollection::factory()->count(9)->forUser($class->user_id)->forSchoolClass($class->id)->create(),
            ...FeeCollection::factory()->count(1)->forUser($class->user_id)->forSchoolClass($class->id)->forOpenContribution()->create(),
        ]);

        $feeCollections->each(function ($feeCollection) use ($class) {
            $pivotData = $class->students->mapWithKeys(function ($student) use ($feeCollection) {
                return [
                    $student->id => [
                        'amount' => $feeCollection->amount == 0
                            ? fake()->randomElement([50, 100, 150, 200, 250, 300, 500])
                            : $feeCollection->amount,
                    ],
                ];
            });

            $feeCollection->students()->attach($pivotData);
        });
    }

    private function generateAssessment(SchoolClass $class, $assessmentTypeId, $count = 5, $maxScoreRange = '15-100'): void
    {
        $names = collect($this->getAssessmentNames()[$assessmentTypeId] ?? []);

        for ($i = 1; $i <= $count; $i++) {
            $date = Carbon::today()->subDays($i);

            $maxScoreRangeArray = explode('-', $maxScoreRange);
            $maxScore = collect(range($maxScoreRangeArray[0], $maxScoreRangeArray[1], 5))->random();

            $assType = AssessmentType::findOrFail($assessmentTypeId);
            $typeName = $assType->name . ' ' . $i;

            $assessment = $class->assessments()->create([
                'user_id'            => $class->user_id,
                'name' => $names->isNotEmpty() ? $names->random() . ' ' . $typeName : 'Assessment ' . $typeName,
                'date'               => $date,
                'assessment_type_id' => $assessmentTypeId,
                'max_score'          => $maxScore,
            ]);

            $pivotData = $class->students->mapWithKeys(function ($student) use ($assessment) {
                $maxScore = $assessment->max_score;
                return [
                    $student->id => [
                        'score' => collect(range((int)($maxScore * .75), $maxScore, 1))->random(),
                    ],
                ];
            });

            $assessment->students()->attach($pivotData);
        }
    }

    private function getAssessmentNames(): array
    {
        return [
            1 => [ // Quiz
                'Short', 'Pop', 'Chapter', 'Unit',
                'Weekly', 'Lesson', 'Topic', 'Daily',
                'Surprise', 'Cumulative', 'Review', 'Section',
            ],
            2 => [ // Exam
                'Long', 'Midterm', 'Final', 'Quarterly',
                'Periodic', 'Unit', 'Chapter', 'Cumulative',
                'Comprehensive', 'Term',
            ],
            3 => [ // Homework
                'Worksheet', 'Take-Home Activity', 'Practice Exercises',
                'Drills', 'Review Sheet', 'Problem Set', 'Study Guide',
                'Reading Assignment', 'Answer Sheet', 'Activity Sheet',
            ],
            4 => [ // Project
                'Group Project', 'Individual Project', 'Diorama',
                'Poster Making', 'Research Paper', 'Book Report',
                'Portfolio', 'Model Making', 'Infographic', 'Multimedia Presentation',
            ],
            5 => [ // Oral
                'Recitation', 'Oral Exam', 'Oral Recitation', 'Board Work',
                'Class Participation', 'Oral Report', 'Show and Tell',
                'Debate', 'Oral Defense', 'Role Play',
            ],
        ];
    }
}
