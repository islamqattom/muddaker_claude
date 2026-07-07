<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): array
    {
        $conversation = $this->message->conversation;

        return $conversation->participants
            ->map(fn ($user) => new PrivateChannel('chat.' . $user->id))
            ->toArray();
    }

    public function broadcastAs(): string
    {
        return 'new-message';
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('attachments');

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id' => $this->message->user_id,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at->toIso8601String(),
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'avatar' => $this->message->user->avatar_url,
            ],
            'attachments' => $this->message->attachments->map(fn ($a) => [
                'id' => $a->id,
                'message_id' => $a->message_id,
                'original_name' => $a->original_name,
                'mime_type' => $a->mime_type,
                'size' => $a->size,
                'category' => $a->category,
                'url' => $a->url,
                'download_url' => $a->download_url,
                'human_size' => $a->human_size,
                'is_image' => $a->is_image,
                'is_video' => $a->is_video,
                'is_audio' => $a->is_audio,
                'icon' => $a->icon,
            ])->values()->all(),
        ];
    }
}