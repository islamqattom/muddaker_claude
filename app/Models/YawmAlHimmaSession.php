<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YawmAlHimmaSession extends Model
{
    protected $table = 'yawm_al_himma_sessions';

    protected $fillable = [
        'supervisor_name',
        'student_id',
        'student_name',
        'parts',
        'pages_data',
        'total_errors',
        'total_hesitations',
        'session_date',
        'notes',
    ];

    protected $casts = [
        'parts' => 'array',
        'pages_data' => 'array',
        'session_date' => 'date',
        'total_errors' => 'integer',
        'total_hesitations' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
