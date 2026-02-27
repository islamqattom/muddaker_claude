<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarningFactory extends Factory
{
    protected $model = Warning::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'issued_by' => User::factory()->supervisor(),
            'warning_number' => 'WARN-' . date('Y') . '-' . fake()->unique()->numerify('####'),
            'type' => fake()->randomElement(['attendance', 'recitation', 'general']),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'issue_date' => fake()->date('Y-m-d'),
            'from_date' => fake()->date('Y-m-d'),
            'to_date' => fake()->date('Y-m-d'),
            'absent_days' => fake()->numberBetween(0, 10),
            'late_days' => fake()->numberBetween(0, 5),
            'total_days' => fake()->numberBetween(0, 15),
            'failed_sessions' => fake()->numberBetween(0, 5),
            'total_sessions' => fake()->numberBetween(5, 20),
            'additional_notes' => null,
            'is_read_by_parent' => false,
        ];
    }

    public function attendance(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'attendance']);
    }

    public function recitation(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'recitation']);
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'general']);
    }

    public function readByParent(): static
    {
        return $this->state(fn (array $attributes) => ['is_read_by_parent' => true]);
    }
}
