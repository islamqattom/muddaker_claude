<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reciter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'style',
        'is_segmented',
        'audio_base_url',
        'is_active',
    ];

    protected $casts = [
        'is_segmented' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function audioSegments()
    {
        return $this->hasMany(AudioSegment::class);
    }
}
