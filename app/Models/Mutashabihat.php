<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutashabihat extends Model
{
    protected $table = 'mutashabihat';

    protected $fillable = [
        'source_surah', 'source_ayah', 'target_surah', 'target_ayah',
        'similarity_score', 'shared_phrase',
    ];

    public $timestamps = true;

    /**
     * Get similar verses for a specific verse
     */
    public static function getForVerse(int $surah, int $ayah): array
    {
        // Get where this verse is the source
        $results = self::where('source_surah', $surah)
            ->where('source_ayah', $ayah)
            ->with(['targetSurah', 'sourceSurah'])
            ->orderByDesc('similarity_score')
            ->get();

        if ($results->isEmpty()) {
            // Try as target
            $results = self::where('target_surah', $surah)
                ->where('target_ayah', $ayah)
                ->orderByDesc('similarity_score')
                ->get();
        }

        return $results->map(function ($item) use ($surah, $ayah) {
            $isSource = $item->source_surah == $surah && $item->source_ayah == $ayah;
            return [
                'surah' => $isSource ? $item->target_surah : $item->source_surah,
                'ayah' => $isSource ? $item->target_ayah : $item->source_ayah,
                'similarity_score' => $item->similarity_score,
                'shared_phrase' => $item->shared_phrase,
            ];
        })->toArray();
    }

    /**
     * Get similar verses for a range (e.g., a page or surah)
     */
    public static function getForSurah(int $surah): array
    {
        $results = self::where('source_surah', $surah)
            ->orderBy('source_ayah')
            ->orderByDesc('similarity_score')
            ->get();

        return $results->groupBy('source_ayah')
            ->map(fn($group) => $group->map(fn($item) => [
                'target_surah' => $item->target_surah,
                'target_ayah' => $item->target_ayah,
                'similarity_score' => $item->similarity_score,
                'shared_phrase' => $item->shared_phrase,
            ])->toArray())
            ->toArray();
    }
}
