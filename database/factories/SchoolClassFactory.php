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
            'tags'       => $this->faker->words(3),
        ];
    }

    /**
     * Allow overriding user_id when needed.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn () => ['user_id' => $userId]);
    }
}
