<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatService
{
    public const MAX_ATTACHMENT_BYTES = 25 * 1024 * 1024;

    public const ALLOWED_MIME_PREFIXES = [
        'image/',
        'video/',
        'audio/',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument',
        'application/vnd.ms-excel',
        'application/vnd.ms-powerpoint',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip',
        'application/json',
        'application/xml',
        'text/',
    ];

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Send a chat message. Either text (content), one or more attachments,
     * or both. Files are stored on the public disk under
     * `chat-attachments/{conversation}/{message}`.
     *
     * @param UploadedFile[] $attachments
     */
    public function sendMessage(Conversation $conversation, User $sender, ?string $content, array $attachments = []): Message
    {
        $content = trim((string) $content);
        if ($content === '' && empty($attachments)) {
            throw new \InvalidArgumentException('لا يمكن إرسال رسالة فارغة بدون مرفقات.');
        }

        $message = DB::transaction(function () use ($conversation, $sender, $content, $attachments) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
                'content' => $content !== '' ? $content : null,
            ]);

            foreach ($attachments as $file) {
                $this->storeAttachment($message, $file);
            }

            $conversation->update(['updated_at' => now()]);

            return $message;
        });

        $message->load('attachments');

        $this->notifyRecipients($conversation, $sender, $message);

        return $message;
    }

    private function storeAttachment(Message $message, UploadedFile $file): MessageAttachment
    {
        $mime = (string) ($file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream');
        $size = (int) $file->getSize();

        if ($size > self::MAX_ATTACHMENT_BYTES) {
            throw new \InvalidArgumentException(
                'حجم المرفق يتجاوز الحد الأقصى (' . self::MAX_ATTACHMENT_BYTES . ' بايت).'
            );
        }

        $isAllowed = false;
        foreach (self::ALLOWED_MIME_PREFIXES as $prefix) {
            if (Str::startsWith($mime, $prefix)) {
                $isAllowed = true;
                break;
            }
        }
        if (!$isAllowed) {
            throw new \InvalidArgumentException("نوع الملف غير مسموح: {$mime}");
        }

        $dir = "chat-attachments/{$message->conversation_id}/{$message->id}";
        $stored = $file->store($dir, 'public');

        $attachment = new MessageAttachment([
            'message_id' => $message->id,
            'disk' => 'public',
            'path' => $stored,
            'original_name' => $file->getClientOriginalName() ?: basename($stored),
            'mime_type' => $mime,
            'size' => $size,
        ]);
        $attachment->category = $attachment->detectCategory();
        $attachment->save();

        return $attachment;
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

        $title = $conversation->is_group
            ? "{$sender->name} في {$conversation->name}"
            : "{$sender->name} أرسل لك رسالة";

        $body = $this->buildPreview($message);
        $link = route('chat.show', $conversation);

        $this->notificationService->sendToUsers(
            $recipientIds,
            'message',
            $title,
            $body,
            $link,
            $message->id,
            $sender,
            [
                'message_preview' => $body,
                'conversation_id' => $conversation->id,
                'has_attachments' => $message->attachments->isNotEmpty(),
                'attachment_count' => $message->attachments->count(),
            ]
        );
    }

    private function buildPreview(Message $message): string
    {
        $parts = [];
        if (!empty($message->content)) {
            $parts[] = Str::limit($message->content, 80);
        }
        foreach ($message->attachments as $att) {
            $parts[] = '📎 ' . $att->original_name;
        }
        return $parts ? implode(' • ', $parts) : 'مرفق';
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
        $now = now();

        $conversation->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => $now, 'last_seen_at' => $now]);

        $updated = Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => $now]);

        Notification::where('user_id', $userId)
            ->whereIn(
                'message_id',
                Message::where('conversation_id', $conversation->id)->pluck('id')
            )
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated > 0) {
            $messageIds = Message::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $userId)
                ->where('read_at', $now)
                ->pluck('id')
                ->toArray();
            try {
                event(new \App\Events\MessageRead(
                    $conversation,
                    \App\Models\User::find($userId),
                    $messageIds
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to broadcast MessageRead', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Heartbeat: update last_seen_at for the user in this conversation.
     * Does NOT mark messages as read. Cheap; called every ~15s while chat is open.
     */
    public function heartbeat(Conversation $conversation, int $userId): void
    {
        $conversation->participants()
            ->where('user_id', $userId)
            ->update(['last_seen_at' => now()]);
    }

    /**
     * Get participants' last_seen / last_read info, for presence display.
     *
     * @return array<int, array{id:int,name:string,last_seen:?string,last_read:?string,is_online:bool}>
     */
    public function getPresence(Conversation $conversation): array
    {
        $now = now();
        return $conversation->participants->map(function ($p) use ($now) {
            $lastSeen = $p->pivot->last_seen_at;
            $lastRead = $p->pivot->last_read_at;
            return [
                'id' => $p->id,
                'name' => $p->name,
                'last_seen' => $lastSeen ? $lastSeen->toIso8601String() : null,
                'last_read' => $lastRead ? $lastRead->toIso8601String() : null,
                'is_online' => $lastSeen ? $lastSeen->gt($now->copy()->subSeconds(60)) : false,
            ];
        })->values()->all();
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

    public function deleteAttachment(MessageAttachment $attachment, User $user): bool
    {
        if ($attachment->message?->user_id !== $user->id) {
            throw new \Exception('لا يمكن حذف مرفق لرسالة لم ترسلها.');
        }
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();
        return true;
    }
}
