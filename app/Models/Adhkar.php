<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adhkar extends Model
{
    protected $table = 'adhkar';

    protected $fillable = ['azkar_category_id', 'title', 'content', 'count'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AzkarCategory::class, 'azkar_category_id');
    }
}