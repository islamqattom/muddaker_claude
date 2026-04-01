<?php

namespace Database\Factories;

use App\Models\LiveRecitationSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class LiveRecitationSessionFactory extends Factory
{
    protected $model = LiveRecitationSession::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'mode' => fake()->randomElement(['page', 'range', 'surah']),
            'start_page' => fake()->numberBetween(1, 604),
            'end_page' => fake()->numberBetween(1, 604),
            'surah' => null,
            'verse_start' => null,
            'verse_end' => null,
            'total_verses' => fake()->numberBetween(1, 20),
            'verses_completed' => 0,
            'total_errors' => 0,
            'average_similarity' => 0.0,
            'final_score' => null,
            'status' => 'active',
            'started_at' => now(),
            'ended_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'ended_at' => now(),
        ]);
    }

    public function surah(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'surah',
            'surah' => fake()->numberBetween(1, 114),
            'verse_start' => 1,
            'verse_end' => fake()->numberBetween(3, 20),
        ]);
    }

    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => 'page',
            'start_page' => fake()->numberBetween(1, 604),
            'end_page' => fake()->numberBetween(1, 604),
        ]);
    }

    public function range(): static
    {
        return $this->state(function (array $attributes) {
            $start = fake()->numberBetween(1, 600);
            return [
                'mode' => 'range',
                'start_page' => $start,
                'end_page' => $start + fake()->numberBetween(1, 10),
            ];
        });
    }
}
