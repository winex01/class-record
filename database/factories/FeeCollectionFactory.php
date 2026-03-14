<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeCollectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'         => 1,
            'school_class_id' => SchoolClass::first()->id,
            'name'            => $this->faker->randomElement([
                'Miscellaneous Fee', 'Field Trip Fee', 'Graduation Fee',
                'School Supply Fee', 'Uniform Fee', 'Library Fee',
                'Laboratory Fee', 'PTA Fee', 'Sports Fee', 'Yearbook Fee',
                'Clearance Fee', 'ID Fee', 'Medical Fee', 'Examination Fee',
            ]),
            'amount'      => $this->faker->randomElement([50, 100, 150, 200, 250, 300, 500, 1000]),
            'date'        => $this->faker->dateTimeBetween('-3 months', 'now'),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Set a specific user for the fee collection.
     */
    public function forUser($userId)
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Set a specific school class for the fee collection.
     */
    public function forSchoolClass($schoolClassId)
    {
        return $this->state(fn (array $attributes) => [
            'school_class_id' => $schoolClassId,
        ]);
    }

    /**
     * Open contribution — amount is 0, voluntary/no fixed price.
     */
    public function forOpenContribution()
    {
        return $this->state(fn (array $attributes) => [
            'amount' => 0,
        ]);
    }
}
