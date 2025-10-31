<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use Faker\Factory as Faker;
use App\Models\AssessmentType;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Winnie Damayo',
            'email' => 'admin@admin.com',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
        ]);


        $this->testData();
    }

    public function testData()
    {
        $faker = Faker::create();

        // create 10 students for user 1
        Student::factory()
            ->count(10)
            ->forUser(1)
            ->create();

        // create a class
        SchoolClass::factory()->count(1)->create();

        // get the class
        $class = SchoolClass::firstOrFail();

        // get student IDs as a collection (not array)
        $students = Student::pluck('id');

        // ✅ attach students to class (if you have students() relationship)
        $class->students()->attach($students);

        // create attendances
        for ($i = 0; $i < 10; $i++) {
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

        // create assessments
        for ($i = 0; $i < 15; $i++) {
            $date = Carbon::today()->subDays($i);

            // random AssessmentType
            $assessmentTypeId = AssessmentType::where('user_id', 1)->inRandomOrder()->value('id');

            // random max_score between 15–100 (multiples of 5)
            $maxScore = collect(range(15, 100, 5))->random();

            // create assessment
            $assessment = $class->assessments()->create([
                'user_id' => $class->user_id,
                'name' => ucfirst($faker->words(2, true)), // e.g., "Grammar Quiz"
                'date' => $date,
                'assessment_type_id' => $assessmentTypeId,
                'max_score' => $maxScore,
            ]);

            // attach students with random scores ≤ max_score
            $pivotData = $class->students->mapWithKeys(function ($student) use ($assessment) {
                return [
                    $student->id => [
                        'score' => rand(0, $assessment->max_score),
                    ],
                ];
            });

            $assessment->students()->attach($pivotData);
        }

    }
}
