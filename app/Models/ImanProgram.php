<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ImanProgram extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'owner_id',
        'owner_type',
        'is_active',
        'start_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(ImanCriterion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function dailyEntries(): HasMany
    {
        return $this->hasMany(ImanDailyEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOwner($query, $owner)
    {
        if ($owner === null) {
            return $query;
        }

        return $query->where('owner_id', $owner->getKey())
            ->where('owner_type', $owner->getMorphClass());
    }
}
