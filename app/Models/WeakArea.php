<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeakArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'surah', 'verse_start', 'verse_end', 'page_number',
        'error_count', 'hesitation_count', 'attempt_count',
        'error_rate', 'hesitation_rate', 'priority_score', 'is_remediated',
    ];

    protected $casts = [
        'error_rate' => 'float',
        'hesitation_rate' => 'float',
        'priority_score' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getIsWeakAttribute(): bool
    {
        return $this->error_rate >= 0.3 || $this->hesitation_rate >= 0.4;
    }
}
