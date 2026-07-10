<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public const AUDITABLE_MODELS = [
        'App\Models\Student',
        'App\Models\User',
        'App\Models\Supervisor',
        'App\Models\ParentProfile',
        'App\Models\Level',
        'App\Models\Attendance',
        'App\Models\RecitationSession',
        'App\Models\Warning',
    ];

    public const AUDIT_ACTIONS = [
        'created',
        'updated',
        'deleted',
        'login',
        'logout',
        'login_failed',
    ];

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeOfModelType($query, $modelType)
    {
        if (in_array($modelType, self::AUDITABLE_MODELS)) {
            return $query->where('model_type', $modelType);
        }
        return $query;
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeFilterByRequest($query, Request $request)
    {
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->action && in_array($request->action, self::AUDIT_ACTIONS)) {
            $query->where('action', $request->action);
        }

        if ($request->model_type) {
            $query->ofModelType($request->model_type);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }
}
