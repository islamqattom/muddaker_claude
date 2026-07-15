<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $reader,
        /** @var int[] */
        public array $messageIds,
    ) {}

    public function broadcastOn(): array
    {
        // Notify the original senders (and any other participant)
        return $this->conversation->participants
            ->filter(fn ($p) => $p->id !== $this->reader->id)
            ->map(fn ($p) => new PrivateChannel('chat.' . $p->id))
            ->values()
            ->toArray();
    }

    public function broadcastAs(): string
    {
        return 'message-read';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'reader' => [
                'id' => $this->reader->id,
                'name' => $this->reader->name,
            ],
            'message_ids' => $this->messageIds,
            'read_at' => now()->toIso8601String(),
        ];
    }
}
