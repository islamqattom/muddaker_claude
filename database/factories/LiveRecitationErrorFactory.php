<?php

namespace Database\Factories;

use App\Models\LiveRecitationError;
use App\Models\LiveRecitationSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class LiveRecitationErrorFactory extends Factory
{
    protected $model = LiveRecitationError::class;

    public function definition(): array
    {
        return [
            'live_session_id' => LiveRecitationSession::factory(),
            'surah' => fake()->numberBetween(1, 114),
            'verse_number' => fake()->numberBetween(1, 286),
            'verse_index' => fake()->numberBetween(0, 10),
            'expected_text' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
            'detected_text' => 'بسم الله الرحمن',
            'similarity' => fake()->randomFloat(2, 50, 100),
            'error_type' => fake()->randomElement(['missing', 'mismatch', 'extra', 'unclear']),
            'word_level_errors' => null,
            'occurred_at' => now(),
        ];
    }

    public function missing(): static
    {
        return $this->state(fn (array $attributes) => ['error_type' => 'missing']);
    }

    public function mismatch(): static
    {
        return $this->state(fn (array $attributes) => ['error_type' => 'mismatch']);
    }

    public function extra(): static
    {
        return $this->state(fn (array $attributes) => ['error_type' => 'extra']);
    }

    public function unclear(): static
    {
        return $this->state(fn (array $attributes) => ['error_type' => 'unclear']);
    }
}
