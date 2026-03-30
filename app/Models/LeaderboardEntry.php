<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'period', 'type', 'rank', 'score', 'period_start', 'period_end',
    ];

    protected $casts = [
        'rank' => 'integer',
        'score' => 'float',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
