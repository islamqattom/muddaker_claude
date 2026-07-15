<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_group', 'avatar', 'created_by'];

    protected $casts = [
        'is_group' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'last_seen_at']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    public function lastMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Message::class)->latest('id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function getUnreadCountAttribute(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant || !$participant->pivot->last_read_at) {
            return $this->messages()->count();
        }
        return $this->messages()->where('created_at', '>', $participant->pivot->last_read_at)->count();
    }

    /**
     * Display title for a single viewer.
     */
    public function displayTitleFor(int $userId): string
    {
        if ($this->is_group) {
            return $this->name ?: 'مجموعة';
        }
        $other = $this->participants->first(fn ($p) => $p->id !== $userId);
        return $other?->name ?: 'مستخدم';
    }

    /**
     * Returns the avatar URL for the conversation from the perspective of a viewer.
     * For groups, returns the group icon; for direct chats, returns the other user's avatar.
     */
    public function displayAvatarFor(int $userId): ?string
    {
        if ($this->is_group) {
            return $this->avatar;
        }
        $other = $this->participants->first(fn ($p) => $p->id !== $userId);
        return $other?->avatar_url ?? null;
    }

    /**
     * True if the given user is currently online (last_seen within 60 seconds).
     */
    public function isUserOnline(int $userId): bool
    {
        $p = $this->participants->firstWhere('id', $userId);
        if (!$p || !$p->pivot->last_seen_at) {
            return false;
        }
        return \Illuminate\Support\Carbon::parse($p->pivot->last_seen_at)->gt(now()->subSeconds(60));
    }

    /**
     * Carbon of the user's last_seen for this conversation, or null.
     */
    public function lastSeenOf(int $userId): ?\Illuminate\Support\Carbon
    {
        $p = $this->participants->firstWhere('id', $userId);
        if (!$p || !$p->pivot->last_seen_at) {
            return null;
        }
        return \Illuminate\Support\Carbon::parse($p->pivot->last_seen_at);
    }
}
