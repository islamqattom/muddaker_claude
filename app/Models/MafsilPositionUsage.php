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
    ];

    protected $casts = [
        'juz_number' => 'integer',
        'difficulty' => 'integer',
    ];

    public function mafsilPosition(): BelongsTo
    {
        return $this->belongsTo(MafsilPosition::class, 'mafsil_position_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
