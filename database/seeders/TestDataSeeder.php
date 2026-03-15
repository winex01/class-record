<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        Student::factory()->count(25)->gender(Gender::MALE->value)->create();
        Student::factory()->count(25)->gender(Gender::FEMALE->value)->create();

        SchoolClass::factory()->count(1)->create();

        $class = SchoolClass::firstOrFail();

        $students = Student::pluck('id');

        $class->students()->attach($students);

        $this->call(SchoolClassDataSeeder::class, parameters: ['classId' => $class->id]);
    }
}
