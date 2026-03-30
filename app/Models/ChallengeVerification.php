<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id', 'student_id', 'recitation_session_id', 'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recitationSession()
    {
        return $this->belongsTo(RecitationSession::class);
    }
}
