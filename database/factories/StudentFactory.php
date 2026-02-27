<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'supervisor_id' => User::factory()->supervisor(),
            'level_id' => Level::factory(),
            'student_number' => fake()->unique()->numberBetween(1000, 9999),
            'national_id' => fake()->unique()->numerify('#############'),
            'birth_date' => fake()->date('Y-m-d', '2015-01-01'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'memorized_parts' => 0,
            'current_part' => 1,
            'current_page' => 1,
            'enrollment_date' => fake()->date('Y-m-d'),
            'notes' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Student $student) {
            if (!$student->user) {
                $student->user()->associate(User::factory()->student()->create());
            }
        });
    }
}
