<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    public const PALETTE = [
        '#d4a030', '#16a085', '#d35400', '#2980b9', '#8e44ad',
        '#c0392b', '#27ae60', '#f39c12', '#2c3e50', '#e74c3c',
        '#1abc9c', '#9b59b6', '#d68910', '#2874a6', '#1e8449',
    ];

    protected $fillable = [
        'created_by',
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function lessonsOrdered(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('lesson_date')->orderBy('start_time');
    }

    public function getLessonsCountAttribute(): int
    {
        return $this->lessons()->count();
    }

    public function getCompletedLessonsCountAttribute(): int
    {
        return $this->lessons()->where('status', 'completed')->count();
    }

    public function getPendingLessonsCountAttribute(): int
    {
        return $this->lessons()->where('status', 'pending')->count();
    }

    public function getProgressPercentAttribute(): float
    {
        $total = $this->lessons()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->lessons()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->lessons()
            ->where('status', 'pending')
            ->where('lesson_date', '<', now()->toDateString())
            ->exists();
    }
}
