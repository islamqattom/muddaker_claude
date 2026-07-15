<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'user_id', 'content', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'message_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Read state of a message from the perspective of a particular user.
     *  - 'sent'    = sender has sent, no one has read yet (single tick)
     *  - 'delivered' = every other participant has loaded the conversation
     *                  (their last_seen_at >= this message's created_at)
     *                  but not necessarily read
     *  - 'read'    = every other participant has last_read_at >= this
     *                  message's created_at (double blue tick)
     *
     * Used for the WhatsApp-style tick marks on a sent bubble.
     */
    public function readStateFor(int $viewerId): string
    {
        if ($this->user_id === $viewerId) {
            $conversation = $this->conversation;
            if (!$conversation) {
                return 'sent';
            }
            $otherParticipants = $conversation->participants->where('id', '!=', $viewerId);
            if ($otherParticipants->isEmpty()) {
                return 'read';
            }
            foreach ($otherParticipants as $p) {
                $lastRead = $p->pivot->last_read_at
                    ? \Illuminate\Support\Carbon::parse($p->pivot->last_read_at)
                    : null;
                if (!$lastRead || $lastRead->lt($this->created_at)) {
                    $lastSeen = $p->pivot->last_seen_at
                        ? \Illuminate\Support\Carbon::parse($p->pivot->last_seen_at)
                        : null;
                    if ($lastSeen && $lastSeen->gte($this->created_at)) {
                        continue;
                    }
                    return 'sent';
                }
            }
            return 'read';
        }
        return $this->read_at ? 'read' : 'delivered';
    }
}
