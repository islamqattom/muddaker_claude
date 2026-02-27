<?php

namespace Database\Factories;

use App\Models\QuranPart;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuranPartFactory extends Factory
{
    protected $model = QuranPart::class;

    public function definition(): array
    {
        return [
            'part_number' => fake()->numberBetween(1, 30),
            'name' => fake()->randomElement(['الجزء الأول', 'الجزء الثاني', 'الجزء الثالث']),
            'pages_count' => fake()->numberBetween(20, 30),
            'start_page' => fake()->numberBetween(1, 550),
            'end_page' => fake()->numberBetween(20, 604),
        ];
    }
}
