<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPartRecord extends Model
{
    public const MASTERY_MASTERED = 'متقن';
    public const MASTERY_NOT_MASTERED = 'غير متقن';
    public const MASTERY_MAFSIL = 'مفصل';

    protected $fillable = [
        'student_id',
        'part_number',
        'is_completed',
        'total_errors',
        'total_hesitations',
        'total_score',
        'last_recitation_date',
        'is_manual_override',
        'mastery_status',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'total_errors' => 'integer',
        'total_hesitations' => 'integer',
        'total_score' => 'decimal:2',
        'last_recitation_date' => 'date',
        'is_manual_override' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}