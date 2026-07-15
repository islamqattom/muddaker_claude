<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $user,
    ) {}

    public function broadcastOn(): array
    {
        // Send to all participants except the typer
        return $this->conversation->participants
            ->filter(fn ($p) => $p->id !== $this->user->id)
            ->map(fn ($p) => new PrivateChannel('chat.' . $p->id))
            ->values()
            ->toArray();
    }

    public function broadcastAs(): string
    {
        return 'user-typing';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}
