<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuranWord extends Model
{
    use HasFactory;

    protected $table = 'quran_words';

    protected $fillable = [
        'surah_number',
        'ayah_number',
        'word_position',
        'text_uthmani',
        'text_imlaei',
        'text_indopak',
        'transliteration',
        'translation_en',
    ];
}
