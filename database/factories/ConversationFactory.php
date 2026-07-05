<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->optional()->sentence(2),
            'is_group' => false,
            'avatar' => null,
            'created_by' => User::factory(),
        ];
    }

    public function group(?string $name = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_group' => true,
            'name' => $name ?? $this->faker->words(2, true),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Conversation $conversation) {
            if ($conversation->participants()->count() === 0) {
                $conversation->participants()->attach($conversation->created_by);
            }
        });
    }
}
