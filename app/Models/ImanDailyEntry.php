<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImanDailyEntry extends Model
{
    protected $fillable = [
        'iman_program_id',
        'iman_criterion_id',
        'entry_date',
        'is_checked',
        'value',
        'note',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_checked' => 'boolean',
        'value' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ImanProgram::class, 'iman_program_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(ImanCriterion::class, 'iman_criterion_id');
    }
}
