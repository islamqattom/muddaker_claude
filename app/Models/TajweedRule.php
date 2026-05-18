<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TajweedRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'order',
        'example_verse_key',
        'audio_url',
        'examples',
    ];

    protected $casts = [
        'examples' => 'array',
        'order' => 'integer',
    ];

    public const CATEGORY_MAKHRAJ = 'مخارج الحروف';
    public const CATEGORY_SIFAT = 'صفات الحروف';
    public const CATEGORY_WAQF = 'الوقف';
    public const CATEGORY_IDGHAM = 'الإدغام';
    public const CATEGORY_QALQALA = 'القلقلة';
}