<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TajweedRule extends Model
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
        'surah_number',
        'ayah_number',
        'word_position',
        'rule_type',
        'start_char',
        'end_char',
        'rule_color',
    ];

    public $timestamps = true;

    /**
     * Get Tajweed rules for a specific verse
     */
    public static function getForVerse(int $surah, int $ayah): array
    {
        return self::where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->orderBy('word_position')
            ->orderBy('start_char')
            ->get()
            ->map(fn($r) => [
                'word_position' => $r->word_position,
                'rule_type' => $r->rule_type,
                'start_char' => $r->start_char,
                'end_char' => $r->end_char,
                'rule_color' => $r->rule_color,
            ])
            ->toArray();
    }

    /**
     * Default colors for Tajweed rules (matching QUL standard)
     */
    public static function getDefaultColors(): array
    {
        return [
            'ghunna' => '#FF7F50',           // Coral - nasalization
            'ikhfa' => '#8A2BE2',            // BlueViolet - hiding
            'idgham' => '#4169E1',           // RoyalBlue - merging
            'idgham_without_ghunnah' => '#6495ED', // CornflowerBlue
            'iqlab' => '#2E8B57',            // SeaGreen - conversion
            'qalqalah' => '#008080',         // Teal - echo
            'madd_2' => '#DC143C',           // Crimson - 2 count prolongation
            'madd_246' => '#B22222',         // FireBrick - 2/4/6 count
            'madd_6' => '#8B0000',           // DarkRed - 6 count
            'madd_obligatory' => '#FF1493',  // DeepPink - obligatory madd
            'slient' => '#708090',           // SlateGray - silent letters
        ];
    }

    /**
     * Arabic labels for Tajweed rules
     */
    public static function getRuleLabels(): array
    {
        return [
            'ghunna' => 'غنة',
            'ikhfa' => 'إخفاء',
            'idgham' => 'إدغام',
            'idgham_without_ghunnah' => 'إدغام بلا غنة',
            'iqlab' => 'إقلاب',
            'qalqalah' => 'قلقلة',
            'madd_2' => 'مد ٢',
            'madd_246' => 'مد ٢/٤/٦',
            'madd_6' => 'مد ٦',
            'madd_obligatory' => 'مد لازم',
            'slient' => 'سكت',
        ];
    }
}
