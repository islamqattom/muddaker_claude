<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class WarningRule extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'type',
        'condition_type',
        'threshold',
        'period_days',
        'level_ids',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'level_ids' => 'array',
        'is_active' => 'boolean',
        'threshold' => 'integer',
        'period_days' => 'integer',
    ];

    public const TYPES = ['attendance', 'recitation', 'general'];
    public const CONDITION_TYPES = ['absent_days', 'late_days', 'failed_sessions', 'not_recitate_count'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(WarningDraft::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isAttendance(): bool
    {
        return $this->type === 'attendance';
    }

    public function isRecitation(): bool
    {
        return $this->type === 'recitation';
    }
}
