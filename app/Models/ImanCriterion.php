<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImanCriterion extends Model
{
    protected $fillable = [
        'iman_program_id',
        'name',
        'type',
        'target_value',
        'sort_order',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'sort_order' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ImanProgram::class, 'iman_program_id');
    }

    public function dailyEntries(): HasMany
    {
        return $this->hasMany(ImanDailyEntry::class);
    }

    public function isCheckType(): bool
    {
        return $this->type === 'check';
    }
}
