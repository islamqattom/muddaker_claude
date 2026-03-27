<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorizationGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'period', 'target_pages', 'is_active',
        'target_days_per_week', 'daily_reminder_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_pages' => 'integer',
        'target_days_per_week' => 'integer',
        'daily_reminder_time' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
