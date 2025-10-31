<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
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
    }
}
