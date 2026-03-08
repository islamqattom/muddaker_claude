<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'supervisor_id' => User::factory()->supervisor(),
            'date' => fake()->date('Y-m-d'),
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused', 'holiday']),
            'arrival_time' => fake()->time('H:i:s'),
            'scheduled_time' => '08:00:00',
            'notes' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'present']);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'absent']);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'late']);
    }

    public function excused(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'excused']);
    }

    public function holiday(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'holiday']);
    }
}
