<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reciter_id',
        'surah_number',
        'ayah_number',
        'word_position',
        'audio_url',
        'start_time',
        'end_time',
        'duration',
    ];

    protected $casts = [
        'start_time' => 'float',
        'end_time' => 'float',
        'duration' => 'float',
    ];

    public function reciter()
    {
        return $this->belongsTo(Reciter::class);
    }
}
