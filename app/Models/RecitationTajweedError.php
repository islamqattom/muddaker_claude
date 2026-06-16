<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecitationTajweedError extends Model
{
    protected $table = 'recitation_tajweed_errors';

    protected $fillable = [
        'recitation_page_id',
        'tajweed_rule_id',
        'category',
        'note',
        'severity',
        'occurrences',
        'source',
    ];

    protected $casts = [
        'severity' => 'integer',
        'occurrences' => 'integer',
    ];

    public function recitationPage(): BelongsTo
    {
        return $this->belongsTo(RecitationPage::class, 'recitation_page_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(TajweedRuleDefinition::class, 'tajweed_rule_id');
    }
}
