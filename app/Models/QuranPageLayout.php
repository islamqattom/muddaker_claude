<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranPageLayout extends Model
{
    protected $table = 'quran_page_layouts';

    protected $fillable = [
        'layout_name', 'page_number', 'surah_number', 'ayah_number',
        'line_number', 'position_in_line', 'is_new_surah',
    ];

    public $timestamps = true;

    /**
     * Get layout for a specific page
     */
    public static function getPageLayout(string $layoutName, int $pageNumber): array
    {
        return self::where('layout_name', $layoutName)
            ->where('page_number', $pageNumber)
            ->orderBy('line_number')
            ->orderBy('position_in_line')
            ->get()
            ->toArray();
    }

    /**
     * Get available layout names
     */
    public static function getAvailableLayouts(): array
    {
        return self::select('layout_name')
            ->distinct()
            ->pluck('layout_name')
            ->toArray();
    }

    /**
     * Check if a layout exists and has data
     */
    public static function hasLayout(string $layoutName): bool
    {
        return self::where('layout_name', $layoutName)->exists();
    }
}
