<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'badge_id', 'earned_at', 'metadata',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function badge()
    {
        return $this->belongsTo(AchievementBadge::class, 'badge_id');
    }
}
