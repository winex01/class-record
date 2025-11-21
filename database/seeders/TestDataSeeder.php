<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AssessmentType;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Factory::create();

        Student::factory()->count(25)->gender(Gender::MALE->value)->create();
        Student::factory()->count(25)->gender(Gender::FEMALE->value)->create();

        // create a class
        SchoolClass::factory()->count(1)->create();

        // get the class
        $class = SchoolClass::firstOrFail();

        // get student IDs as a collection (not array)
        $students = Student::pluck('id');

        // ✅ attach students to class (if you have students() relationship)
        $class->students()->attach($students);

        // create attendances
        for ($i = 0; $i < 90; $i++) {
            $date = Carbon::today()->subDays($i);

            // create attendance record for this date
            $attendance = $class->attendances()->create([
                'user_id' => $class->user_id,
                'date' => $date,
            ]);

            // attach all students with random 'present' value (80% chance true)
            $attachData = $students->mapWithKeys(function ($studentId) {
                return [
                    $studentId => ['present' => mt_rand(1, 100) <= 80], // 80% true
                ];
            });

            $attendance->students()->attach($attachData);
        }

        $this->generateAssessment($class, 1, 20, '15-30'); // Quiz
        $this->generateAssessment($class, 2, 4, '50-70'); // Exam
        $this->generateAssessment($class, 3, 4, '40-50'); // Homework
        $this->generateAssessment($class, 4, 4, '50-60'); // Project
        $this->generateAssessment($class, 5, 4, '50-80'); // oral

        // lesson
        Lesson::factory()->count(50)->forUser(1)->forSchoolClass(1)->create();
    }

    private function generateAssessment(SchoolClass $class, $assessmentTypeId, $count = 5, $maxScoreRange = '15-100')
    {
        // create assessments
        for ($i = 1; $i <= $count; $i++) {
            $date = Carbon::today()->subDays($i);

            $maxScoreRangeArray = explode('-', $maxScoreRange);

            $maxScore = collect(range($maxScoreRangeArray[0], $maxScoreRangeArray[1], 5))->random();

            // create assessment
            $assessment = $class->assessments()->create([
                'user_id' => $class->user_id,
                'name' => AssessmentType::findOrFail($assessmentTypeId)->name.' '. $i,
                'date' => $date,
                'assessment_type_id' => $assessmentTypeId,
                'max_score' => $maxScore,
            ]);

            // attach students with random scores ≤ max_score
            $pivotData = $class->students->mapWithKeys(function ($student) use ($assessment) {
                $maxScore = $assessment->max_score;
                return [
                    $student->id => [
                        'score' => collect(range((int)($maxScore * .75), $maxScore, 1))->random(),
                    ],
                ];
            });

            $assessment->students()->attach($pivotData);
        }// end quiz
    }
}
