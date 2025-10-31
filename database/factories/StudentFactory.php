<?php

namespace Database\Factories;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{

    /**
     * Run the factory
     *
     * Usage in artisan tinker:
     * Student::factory()->forUser(3)->count(10)->create();
     */
    public function definition(): array
    {
        return [
            'user_id' => 1, // default
            'last_name' => $this->faker->lastName,
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->optional()->firstName,
            'suffix_name' => $this->faker->optional()->suffix,
            'gender' => $this->faker->randomElement(Gender::cases())->value,
            'email' => $this->faker->unique()->safeEmail,
            'birth_date' => $this->faker->dateTimeBetween('-20 years', '-10 years')->format('Y-m-d'),
            'contact_number' => $this->faker->optional()->numerify('09#########'),
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
