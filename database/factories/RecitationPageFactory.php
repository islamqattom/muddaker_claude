<?php

namespace Database\Factories;

use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Models\QuranPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecitationPageFactory extends Factory
{
    protected $model = RecitationPage::class;

    public function definition(): array
    {
        return [
            'session_id' => RecitationSession::factory(),
            'page_id' => QuranPage::factory(),
            'page_number' => $this->faker->numberBetween(1, 604),
            'errors' => $this->faker->numberBetween(0, 3),
            'hesitations' => $this->faker->numberBetween(0, 6),
            'color_status' => $this->faker->randomElement(['green', 'orange', 'red', 'black']),
            'is_passed' => $this->faker->boolean(80),
            'page_score' => $this->faker->randomFloat(2, 50, 100),
        ];
    }
}
