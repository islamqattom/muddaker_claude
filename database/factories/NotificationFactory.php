<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['info', 'warning', 'success', 'error', 'message']),
            'link' => null,
            'is_read' => false,
            'message_id' => null,
        ];
    }

    public function forMessage(Message $message): static
    {
        return $this->state(fn (array $attributes) => [
            'message_id' => $message->id,
            'type' => 'message',
            'link' => route('chat.show', $message->conversation_id),
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => ['is_read' => true]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => ['is_read' => false]);
    }
}
