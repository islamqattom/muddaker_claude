<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveRecitationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'recitation_session_id',
        'mode',
        'start_page',
        'end_page',
        'surah',
        'verse_start',
        'verse_end',
        'total_verses',
        'verses_completed',
        'total_errors',
        'average_similarity',
        'final_score',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'average_similarity' => 'float',
        'final_score' => 'float',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recitationSession()
    {
        return $this->belongsTo(RecitationSession::class);
    }

    public function errors()
    {
        return $this->hasMany(LiveRecitationError::class, 'live_session_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'نشط',
            'paused' => 'متوقف',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
            default => $this->status,
        };
    }

    public function getModeLabelAttribute(): string
    {
        return match($this->mode) {
            'page' => 'صفحة',
            'range' => 'نطاق',
            'surah' => 'سورة',
            default => $this->mode,
        };
    }
}
