<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'surah', 'verse_start', 'verse_end', 'page_number',
        'ease_factor', 'interval', 'repetitions', 'next_review_date', 'last_reviewed_at',
    ];

    protected $casts = [
        'next_review_date' => 'date',
        'last_reviewed_at' => 'datetime',
        'ease_factor' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reviews()
    {
        return $this->hasMany(RecitationReview::class);
    }

    public function getIsDueAttribute(): bool
    {
        if (!$this->next_review_date) {
            return true;
        }
        return $this->next_review_date->lte(\Carbon\Carbon::today());
    }

    public function getUrgencyAttribute(): string
    {
        if (!$this->next_review_date) {
            return 'overdue';
        }

        $today = \Carbon\Carbon::today();
        $diff = $today->diffInDays($this->next_review_date, false);

        if ($diff < 0) {
            return 'overdue';
        } elseif ($diff === 0) {
            return 'due_today';
        } elseif ($diff <= 2) {
            return 'upcoming';
        }
        return 'future';
    }
}
