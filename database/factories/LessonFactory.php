<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\LessonStatus;
use App\Models\SchoolClass;
use Relaticle\Flowforge\Services\DecimalPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    private static array $statusCounters = [
        LessonStatus::TOPICS->value => 0,
        LessonStatus::IN_PROGRESS->value => 0,
        LessonStatus::DONE->value => 0,
    ];
    private static array $lastPositions = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            LessonStatus::TOPICS->value,
            LessonStatus::IN_PROGRESS->value,
            LessonStatus::DONE->value,
        ]);

        return [
            'user_id' => User::first()->id,
            'school_class_id' => SchoolClass::first()->id,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'tags' => $this->faker->randomElements([
                        'Apple', 'Banana', 'Orange', 'Grape', 'Strawberry', 'Blueberry',
                        'Pineapple', 'Mango', 'Watermelon', 'Kiwi', 'Peach', 'Pear',
                        'Cherry', 'Raspberry', 'Blackberry', 'Lemon', 'Lime', 'Coconut',
                        'Pomegranate', 'Avocado', 'Fig', 'Date', 'Papaya', 'Guava',
                        'Lychee', 'Dragonfruit', 'Passionfruit', 'Apricot', 'Plum', 'Nectarine'
                    ], 1),
            'status' => $status,
            'completion_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'position' => $this->generatePositionForStatus($status),
        ];
    }

    /**
     * Set a specific user for the lesson.
     */
    public function forUser($userId)
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Set a specific school class for the lesson.
     */
    public function forSchoolClass($schoolClassId)
    {
        return $this->state(fn (array $attributes) => [
            'school_class_id' => $schoolClassId,
        ]);
    }

    private function generatePositionForStatus(string $status): string
    {
        if (self::$statusCounters[$status] === 0) {
            $position = DecimalPosition::forEmptyColumn();
        } else {
            $position = isset(self::$lastPositions[$status])
                ? DecimalPosition::after(self::$lastPositions[$status])
                : DecimalPosition::forEmptyColumn();
        }

        self::$statusCounters[$status]++;
        self::$lastPositions[$status] = $position;

        return $position;
    }
}
