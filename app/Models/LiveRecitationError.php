<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveRecitationError extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_session_id',
        'surah',
        'verse_number',
        'verse_index',
        'expected_text',
        'detected_text',
        'similarity',
        'error_type',
        'word_level_errors',
        'occurred_at',
    ];

    protected $casts = [
        'similarity' => 'float',
        'word_level_errors' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(LiveRecitationSession::class, 'live_session_id');
    }

    public function getErrorTypeLabelAttribute(): string
    {
        return match($this->error_type) {
            'missing' => 'كلمة مفقودة',
            'mismatch' => 'كلمة مختلفة',
            'extra' => 'كلمة زائدة',
            'unclear' => 'غير واضح',
            default => $this->error_type,
        };
    }
}
