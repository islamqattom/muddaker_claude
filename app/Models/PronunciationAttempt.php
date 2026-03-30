<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PronunciationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'surah', 'verse_start', 'verse_end', 'audio_url',
        'assemblyai_response', 'tajweed_score', 'error_segments',
        'status', 'error_message',
    ];

    protected $casts = [
        'assemblyai_response' => 'array',
        'error_segments' => 'array',
        'tajweed_score' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'في الانتظار',
            'processing' => 'قيد المعالجة',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
            default => $this->status,
        };
    }
}
