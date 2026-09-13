<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditObserver
{
    public const SENSITIVE_FIELDS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'api_token',
        'access_token',
    ];

    public function created($model): void
    {
        $this->logAudit($model, 'created', null, $model->getAttributes());
    }

    public function updated($model): void
    {
        $changes = $model->getChanges();
        $original = array_intersect_key($model->getOriginal(), $changes);
        $this->logAudit($model, 'updated', $original, $changes);
    }

    public function deleting($model): void
    {
        $this->logAudit($model, 'deleted', $model->getAttributes(), null);
    }

    protected function logAudit($model, string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role ?? 'system',
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'old_values' => $this->scrubSensitive($oldValues),
            'new_values' => $this->scrubSensitive($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit(request()->userAgent(), 500),
        ]);
    }

    protected function scrubSensitive(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach (self::SENSITIVE_FIELDS as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }
}
