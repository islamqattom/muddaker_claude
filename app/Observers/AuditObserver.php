<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditObserver
{
    public function created($model): void
    {
        $this->logAudit($model, 'created', null, $model->getAttributes());
    }

    public function updated($model): void
    {
        $original = $model->getOriginal();
        $this->logAudit($model, 'updated', $original, $model->getAttributes());
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
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit(request()->userAgent(), 500),
        ]);
    }
}
