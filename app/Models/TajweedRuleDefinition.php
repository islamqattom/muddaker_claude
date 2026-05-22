<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TajweedRuleDefinition extends Model
{
    const CATEGORY_MAKHRAJ = 'مخارج الحروف';
    const CATEGORY_SIFAT = 'صفات الحروف';
    const CATEGORY_WAQF = 'الوقف والابتداء';
    const CATEGORY_MADD = 'المد';
    const CATEGORY_NOON_SAKIN = 'النون الساكنة والتنوين';
    const CATEGORY_MEEM_SAKIN = 'الميم الساكنة';
    const CATEGORY_GHUNNA = 'الغنة';
    const CATEGORY_IDGHAM = 'الإدغام';
    const CATEGORY_QALQALA = 'القلقلة';

    protected $fillable = [
        'name',
        'description',
        'category',
        'order',
        'example_verse_key',
        'audio_url',
        'examples',
        'colored_examples',
    ];

    protected $casts = [
        'examples' => 'array',
        'colored_examples' => 'array',
    ];

    public $timestamps = true;
}
