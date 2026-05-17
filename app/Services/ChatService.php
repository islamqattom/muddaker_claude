<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function sendMessage(Conversation $conversation, User $sender, string $content): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'content' => $content,
        ]);

        $conversation->update(['updated_at' => now()]);

        return $message;
    }

    public function getUnreadCount(int $userId): int
    {
        return Message::whereHas('conversation.participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('user_id', '!=', $userId)
        ->whereNull('read_at')
        ->count();
    }

    public function markConversationAsRead(Conversation $conversation, int $userId): void
    {
        $conversation->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function createGroupConversation(array $userIds, string $name, User $creator): Conversation
    {
        $conversation = Conversation::create([
            'name' => $name,
            'is_group' => true,
            'created_by' => $creator->id,
        ]);

        $conversation->participants()->attach(array_unique(array_merge([$creator->id], $userIds)));

        return $conversation;
    }

    public function addParticipantToGroup(Conversation $conversation, User $user): void
    {
        if (!$conversation->is_group) {
            throw new \Exception('Cannot add participants to a non-group conversation');
        }

        if ($conversation->participants()->where('user_id', $user->id)->exists()) {
            throw new \Exception('User is already a participant');
        }

        $conversation->participants()->attach($user->id);
        $conversation->update(['updated_at' => now()]);
    }

    public function removeParticipantFromGroup(Conversation $conversation, User $user): void
    {
        if (!$conversation->is_group) {
            throw new \Exception('Cannot remove participants from a non-group conversation');
        }

        if ($user->id === $conversation->created_by) {
            throw new \Exception('Cannot remove the group creator');
        }

        $conversation->participants()->detach($user->id);
        $conversation->update(['updated_at' => now()]);
    }
}