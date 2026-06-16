<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TajweedQuizAnswer extends Model
{
    protected $table = 'tajweed_quiz_answers';

    protected $fillable = [
        'attempt_id',
        'tajweed_rule_id',
        'question_text',
        'correct_answer',
        'student_answer',
        'is_correct',
        'time_taken_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'time_taken_seconds' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TajweedQuizAttempt::class, 'attempt_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(TajweedRuleDefinition::class, 'tajweed_rule_id');
    }
}
