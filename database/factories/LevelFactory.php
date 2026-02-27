<?php

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

class LevelFactory extends Factory
{
    protected $model = Level::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['المستوى الأول', 'المستوى الثاني', 'المستوى الثالث', 'المستوى الرابع', 'المستوى الخامس']),
            'level_number' => fake()->numberBetween(1, 10),
            'new_memorization_pages' => fake()->numberBetween(1, 10),
            'review_pages' => fake()->numberBetween(5, 20),
            'description' => fake()->sentence(),
        ];
    }
}
