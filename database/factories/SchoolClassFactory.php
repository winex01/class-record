<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        $subjects = [
            'Mathematics',
            'Science',
            'English',
            'History',
            'Geography',
            'Filipino',
            'Computer Studies',
            'Physical Education',
            'Economics',
            'Music',
            'Arts',
        ];


        return [
            'user_id'    => 1,
            'name'       => $this->faker->randomElement($subjects),
            'date_start' => $this->faker->optional()->date(),
            'date_end'   => $this->faker->optional()->date(),
            'year_section' => $this->yearAndSection(),

        ];
    }

    public function yearAndSection()
    {
        $years = [
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
            'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11',
            '1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'
        ];

        $sections = ['A', 'B', 'C', 'D']; // or [1, 2, 3] or ['Apple', 'Banana', 'Orange']

        $combinations = [];

        foreach ($years as $year) {
            foreach ($sections as $section) {
                $combinations[] = $year . ' - ' . $section; // e.g., "Grade 1 - A", "1st Year - B"
            }
        }

        return $this->faker->randomElement($combinations);
    }
}
