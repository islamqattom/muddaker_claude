<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'key_p256dh',
        'key_auth',
        'user_agent',
        'device_name',
        'expires_at',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Derive a short device label from the user agent so the user can
     * see which devices are subscribed in the settings page.
     */
    public function deviceLabel(): string
    {
        if ($this->device_name) {
            return $this->device_name;
        }
        $ua = $this->user_agent ?? '';
        $os = 'Unknown';
        $browser = 'Browser';

        if (preg_match('/iPhone|iPad|iPod/', $ua)) {
            $os = preg_match('/iPad/', $ua) ? 'iPad' : 'iPhone';
            $browser = 'Safari';
        } elseif (preg_match('/Android/', $ua)) {
            $os = 'Android';
            $browser = preg_match('/Chrome/', $ua) ? 'Chrome' : (preg_match('/Firefox/', $ua) ? 'Firefox' : 'Browser');
        } elseif (preg_match('/Windows/', $ua)) {
            $os = 'Windows';
            $browser = preg_match('/Edg/', $ua) ? 'Edge' : (preg_match('/Firefox/', $ua) ? 'Firefox' : 'Chrome');
        } elseif (preg_match('/Mac OS X/', $ua)) {
            $os = 'macOS';
            $browser = preg_match('/Firefox/', $ua) ? 'Firefox' : (preg_match('/Edg/', $ua) ? 'Edge' : 'Safari');
        } elseif (preg_match('/Linux/', $ua)) {
            $os = 'Linux';
            $browser = preg_match('/Firefox/', $ua) ? 'Firefox' : 'Chrome';
        }

        return "{$browser} • {$os}";
    }
}
