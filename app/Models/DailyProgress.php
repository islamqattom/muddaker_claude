<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'date', 'pages_memorized', 'pages_reviewed',
        'minutes_practiced', 'verses_recited', 'goal_completed', 'streak_continued',
    ];

    protected $casts = [
        'date' => 'date',
        'pages_memorized' => 'integer',
        'pages_reviewed' => 'integer',
        'minutes_practiced' => 'integer',
        'verses_recited' => 'integer',
        'goal_completed' => 'boolean',
        'streak_continued' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }
}
