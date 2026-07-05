<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'completed_at',
    ];

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

    public function assigneeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assigneeStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'assignee_id');
    }

    public function getAssigneeAttribute()
    {
        if ($this->assignee_type === 'student') {
            return $this->assigneeStudent;
        }
        return $this->assigneeUser;
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
