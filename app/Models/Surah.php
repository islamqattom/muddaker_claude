<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'total_ayahs',
        'surah_number',
    ];

    public function ayahs()
    {
        return $this->hasMany(Ayah::class);
    }
}
