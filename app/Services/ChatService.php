<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function sendMessage(Conversation $conversation, User $sender, string $content): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'content' => $content,
        ]);

        $conversation->update(['updated_at' => now()]);

        $this->notifyRecipients($conversation, $sender, $message);

        return $message;
    }

    private function notifyRecipients(Conversation $conversation, User $sender, Message $message): void
    {
        $recipientIds = $conversation->participants()
            ->where('user_id', '!=', $sender->id)
            ->pluck('users.id')
            ->toArray();

        if (empty($recipientIds)) {
            return;
        }

        // For 1:1 chats show the sender's name in the title; for groups show
        // the group name. Body is always the message preview so the user
        // can read the gist without opening the app.
        $title = $conversation->is_group
            ? "{$sender->name} في {$conversation->name}"
            : "{$sender->name} أرسل لك رسالة";

        $preview = Str::limit($message->content, 120);
        $link = route('chat.show', $conversation);

        $this->notificationService->sendToUsers(
            $recipientIds,
            'message',
            $title,
            $preview,
            $link,
            $message->id,
            $sender,
            [
                'message_preview' => $preview,
                'conversation_id' => $conversation->id,
            ]
        );
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

        Notification::where('user_id', $userId)
            ->whereIn(
                'message_id',
                Message::where('conversation_id', $conversation->id)->pluck('id')
            )
            ->where('is_read', false)
            ->update(['is_read' => true]);
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