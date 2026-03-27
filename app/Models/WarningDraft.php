<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'warning_rule_id',
        'student_id',
        'supervisor_id',
        'status',
        'title',
        'content',
        'issue_date',
        'from_date',
        'to_date',
        'attendance_data',
        'recitation_data',
        'test_data',
        'triggered_value',
        'triggered_threshold',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'attendance_data' => 'array',
        'recitation_data' => 'array',
        'test_data' => 'array',
        'triggered_value' => 'integer',
        'triggered_threshold' => 'integer',
        'issue_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'dismissed'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WarningRule::class, 'warning_rule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
