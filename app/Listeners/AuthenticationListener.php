<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthenticationListener
{
    public function handleLogin(Login $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->id,
            'role' => $event->user->role,
            'action' => 'login',
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => ['email' => $event->user->email],
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit(request()->userAgent(), 500),
            'created_at' => now(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role ?? 'system',
            'action' => 'logout',
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit(request()->userAgent(), 500),
            'created_at' => now(),
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        AuditLog::create([
            'user_id' => null,
            'role' => 'system',
            'action' => 'login_failed',
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => [
                'email' => $event->credentials['email'] ?? null,
                'ip' => request()->ip(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit(request()->userAgent(), 500),
            'created_at' => now(),
        ]);
    }
}
