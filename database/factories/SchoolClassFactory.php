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
            'tags' => $this->fruitTags(3),

        ];
    }

    public function fruitTags($count = 3)
    {
        $fruits = [
            'Apple', 'Banana', 'Orange', 'Grape', 'Strawberry', 'Blueberry',
            'Pineapple', 'Mango', 'Watermelon', 'Kiwi', 'Peach', 'Pear',
            'Cherry', 'Raspberry', 'Blackberry', 'Lemon', 'Lime', 'Coconut',
            'Pomegranate', 'Avocado', 'Fig', 'Date', 'Papaya', 'Guava',
            'Lychee', 'Dragonfruit', 'Passionfruit', 'Apricot', 'Plum', 'Nectarine'
        ];

        return $this->faker->randomElements($fruits, $count);
    }
}
