<?php

namespace Database\Factories;

use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecitationSessionFactory extends Factory
{
    protected $model = RecitationSession::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'supervisor_id' => User::factory()->supervisor(),
            'type' => fake()->randomElement(['new_memorization', 'review', 'unmastered', 'recitation', 'mafsil']),
            'part_id' => QuranPart::factory(),
            'start_page' => fake()->numberBetween(1, 600),
            'end_page' => fake()->numberBetween(1, 604),
            'total_pages' => fake()->numberBetween(1, 20),
            'total_errors' => fake()->numberBetween(0, 10),
            'total_hesitations' => fake()->numberBetween(0, 20),
            'status' => fake()->randomElement(['passed', 'failed', 'in_progress']),
            'mafsil_type' => fake()->optional()->numberBetween(1, 30),
            'max_score' => fake()->randomFloat(2, 70, 100),
            'final_score' => fake()->randomFloat(2, 0, 100),
            'unmastered_pages_count' => fake()->numberBetween(0, 5),
            'session_date' => fake()->date('Y-m-d'),
            'notes' => null,
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'passed']);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'failed']);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'in_progress']);
    }
}
