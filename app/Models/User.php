<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'password', 'role', 'avatar', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function supervisor()
    {
        return $this->hasOne(Supervisor::class);
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentProfile::class, 'user_id');
    }

    public function supervisedStudents()
    {
        return $this->hasMany(Student::class, 'supervisor_id');
    }

    public function supervisedExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'supervisor_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return asset('images/default-avatar.jpg');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function pushSubscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function activePushSubscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->pushSubscriptions()->where('is_active', true);
    }

    public function notificationPreferences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Returns true if the user has at least one active, non-expired push
     * subscription — i.e. push notifications are actually deliverable.
     */
    public function hasActivePush(): bool
    {
        return $this->activePushSubscriptions()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Returns true if the user has explicitly disabled push for a given
     * notification type. Defaults to "enabled" when no preference row exists.
     */
    public function isPushEnabledFor(string $type): bool
    {
        $pref = $this->notificationPreferences()->where('type', $type)->first();
        return $pref ? (bool) $pref->push_enabled : true;
    }
}
