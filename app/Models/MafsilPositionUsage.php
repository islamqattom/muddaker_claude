<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MafsilPositionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'juz_number',
        'difficulty',
        'mafsil_position_id',
        'supervisor_id',
        'student_id',
        'completed_at',
    ];

    protected $casts = [
        'juz_number' => 'integer',
        'difficulty' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function mafsilPosition(): BelongsTo
    {
        return $this->belongsTo(MafsilPosition::class, 'mafsil_position_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('completed_at');
    }
}