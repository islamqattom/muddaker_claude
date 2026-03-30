<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenger_id', 'challenged_id', 'surah', 'verse_start', 'verse_end',
        'target_date', 'status', 'winner_id',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function challenger()
    {
        return $this->belongsTo(Student::class, 'challenger_id');
    }

    public function challenged()
    {
        return $this->belongsTo(Student::class, 'challenged_id');
    }

    public function winner()
    {
        return $this->belongsTo(Student::class, 'winner_id');
    }

    public function verifications()
    {
        return $this->hasMany(ChallengeVerification::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'في انتظار القبول',
            'accepted' => 'مقبول - قيد التنفيذ',
            'declined' => 'مرفوض',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
            default => $this->status,
        };
    }

    public function isParticipant(Student $student): bool
    {
        return $this->challenger_id === $student->id || $this->challenged_id === $student->id;
    }
}
