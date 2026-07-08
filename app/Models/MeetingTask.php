<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class MeetingTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_minute_id',
        'assignee_id',
        'assignee_type',
        'assigned_by',
        'title',
        'description',
        'due_date',
        'due_time',
        'end_time',
        'status',
        'notes',
        'color',
        'completed_at',
    ];

    public const STATUS_COLOR_MAP = [
        'pending' => null,
        'in_progress' => '#3498db',
        'partially_done' => '#8e44ad',
        'completed' => '#27ae60',
        'cancelled' => '#7f8c8d',
        'overdue' => '#c0392b',
    ];

    public const TASK_PALETTE = [
        '#16a085', '#d35400', '#2980b9', '#8e44ad', '#c0392b',
        '#27ae60', '#f39c12', '#2c3e50', '#e74c3c', '#1abc9c',
        '#9b59b6', '#d68910', '#2874a6', '#1e8449', '#a93226',
        '#6c3483', '#0e6655', '#b7950b', '#922b21', '#1a5276',
    ];

    public static function generateUniqueColor(): string
    {
        $usedColors = self::whereNotNull('color')->pluck('color')->all();
        $available = array_diff(self::TASK_PALETTE, $usedColors);
        if (!empty($available)) {
            return $available[array_rand($available)];
        }
        return self::TASK_PALETTE[array_rand(self::TASK_PALETTE)];
    }

    public static function colorForStatus(string $status): ?string
    {
        return self::STATUS_COLOR_MAP[$status] ?? null;
    }

    protected $casts = [
        'due_date' => 'date',
        'due_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'completed_at' => 'datetime',
    ];

    public function meetingMinute(): BelongsTo
    {
        return $this->belongsTo(MeetingMinute::class);
    }

    public function assignee(): MorphTo
    {
        return $this->morphTo();
    }

    public function assigneeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assigneeStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'assignee_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'partially_done' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getEffectiveColorAttribute(): string
    {
        if ($this->is_overdue) {
            return self::STATUS_COLOR_MAP['overdue'];
        }
        $statusColor = self::colorForStatus($this->status);
        if ($statusColor !== null) {
            return $statusColor;
        }
        return $this->color ?: '#d4a030';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'لم تتم',
            'in_progress' => 'قيد التنفيذ',
            'partially_done' => 'منجزة جزئياً',
            'completed' => 'منجزة',
            'cancelled' => 'ملغاة',
            default => 'غير معروف'
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'], true)) {
            return false;
        }
        if ($this->due_date && $this->due_date->isPast()) {
            return true;
        }
        return false;
    }

    public function getAssigneeNameAttribute(): string
    {
        if ($this->assignee_type === 'student' && $this->assignee) {
            return $this->assignee->user->name ?? $this->assignee->name;
        }
        return $this->assignee->name ?? '-';
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where(function ($sub) use ($userId) {
                $sub->where('assignee_type', 'user')
                    ->where('assignee_id', $userId);
            })->orWhere(function ($sub) use ($userId) {
                $sub->where('assignee_type', 'student')
                    ->whereHas('assigneeStudent', function ($q2) use ($userId) {
                        $q2->where('user_id', $userId);
                    });
            });
        });
    }
}
