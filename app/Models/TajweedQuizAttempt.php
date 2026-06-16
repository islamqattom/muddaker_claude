<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TajweedQuizAttempt extends Model
{
    protected $table = 'tajweed_quiz_attempts';

    protected $fillable = [
        'student_id',
        'total_questions',
        'correct_answers',
        'score_percentage',
        'duration_seconds',
        'category_filter',
        'questions_payload',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'questions_payload' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'score_percentage' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TajweedQuizAnswer::class, 'attempt_id');
    }
}
