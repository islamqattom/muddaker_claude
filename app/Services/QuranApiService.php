<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QuranApiService
{
    private const BASE_URL = 'https://api.alquran.cloud/v1';
    private const AUDIO_BASE = 'https://cdn.islamic.network/quran/audio/128/ar.alafasy';
    private const CACHE_TTL = 60 * 60 * 24 * 30; // 30 days

    // Juz data: [start_page, page_count] indexed by juz number (1-30)
    private const JUZ_DATA = [
        1 => [1, 21], 2 => [22, 20], 3 => [42, 20], 4 => [62, 20], 5 => [82, 20],
        6 => [102, 20], 7 => [122, 20], 8 => [142, 20], 9 => [162, 20], 10 => [182, 20],
        11 => [202, 20], 12 => [222, 20], 13 => [242, 20], 14 => [262, 19], 15 => [282, 20],
        16 => [302, 19], 17 => [322, 20], 18 => [342, 19], 19 => [362, 20], 20 => [382, 20],
        21 => [402, 20], 22 => [422, 18], 23 => [442, 18], 24 => [462, 19], 25 => [482, 20],
        26 => [502, 19], 27 => [522, 19], 28 => [542, 18], 29 => [558, 20], 30 => [578, 27],
    ];

    public const JUZ_PAGE_RANGES = [
        1 => [1, 21], 2 => [22, 41], 3 => [42, 62], 4 => [63, 82], 5 => [83, 103],
        6 => [104, 124], 7 => [125, 144], 8 => [145, 164], 9 => [165, 185], 10 => [186, 205],
        11 => [206, 226], 12 => [227, 246], 13 => [247, 266], 14 => [267, 285], 15 => [286, 306],
        16 => [307, 325], 17 => [326, 345], 18 => [346, 364], 19 => [365, 384], 20 => [385, 404],
        21 => [405, 424], 22 => [425, 444], 23 => [445, 462], 24 => [463, 481], 25 => [482, 501],
        26 => [502, 520], 27 => [521, 539], 28 => [540, 557], 29 => [558, 577], 30 => [578, 604],
    ];

    public function getPage(int $pageNumber): array
    {
        $cacheKey = "quran_page_{$pageNumber}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($pageNumber) {
            $response = Http::get(self::BASE_URL . "/page/{$pageNumber}/quran-quranuthmani");

            if ($response->failed()) {
                Log::error('QuranApiService: Failed to fetch page', [
                    'page' => $pageNumber,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception("Failed to fetch Quran page: {$pageNumber}");
            }

            return $response->json()['data'];
        });
    }

    public function getPart(int $partNumber): array
    {
        $cacheKey = "quran_part_{$partNumber}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($partNumber) {
            $response = Http::get(self::BASE_URL . "/juz/{$partNumber}/quran-quranuthmani");

            if ($response->failed()) {
                Log::error('QuranApiService: Failed to fetch part', [
                    'part' => $partNumber,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception("Failed to fetch Quran part: {$partNumber}");
            }

            return $response->json()['data'];
        });
    }

    public function getAudioUrls(int $pageNumber): array
    {
        $cacheKey = "quran_audio_{$pageNumber}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($pageNumber) {
            $pageData = $this->getPage($pageNumber);
            $urls = [];

            foreach ($pageData['ayahs'] as $verse) {
                $surah = $verse['surah']['number'];
                $verseInSurah = $verse['numberInSurah'];
                $verseKey = "{$surah}:{$verseInSurah}";
                $urls[$verseKey] = self::AUDIO_BASE . "/{$verse['number']}.mp3";
            }

            return $urls;
        });
    }

    public function getVerseAudioUrl(int $verseNumber): string
    {
        return self::AUDIO_BASE . "/{$verseNumber}.mp3";
    }

    /**
     * Get a specific verse (surah:verse)
     *
     * @param int $surah Surah number (1-114)
     * @param int $verse Verse number in surah
     * @return array
     */
    public function getVerse(int $surah, int $verse): array
    {
        $cacheKey = "quran_verse_{$surah}_{$verse}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($surah, $verse) {
            $response = Http::get(self::BASE_URL . "/ayah/{$surah}:{$verse}/quran-quranuthmani");

            if ($response->failed()) {
                Log::error('QuranApiService: Failed to fetch verse', [
                    'surah' => $surah,
                    'verse' => $verse,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception("Failed to fetch verse {$surah}:{$verse}");
            }

            $data = $response->json()['data'] ?? [];
            return $this->mapVerseData($data, $surah, $verse);
        });
    }

    /**
     * Get a range of verses
     *
     * @param int $surah Surah number (1-114)
     * @param int $verseStart Start verse
     * @param int $verseEnd End verse (inclusive)
     * @return array
     */
    public function getVerses(int $surah, int $verseStart, int $verseEnd): array
    {
        $cacheKey = "quran_verses_{$surah}_{$verseStart}_{$verseEnd}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($surah, $verseStart, $verseEnd) {
            $response = Http::get(
                self::BASE_URL . "/ayah/{$surah}:{$verseStart}-{$verseEnd}/quran-quranuthmani"
            );

            if ($response->failed()) {
                Log::error('QuranApiService: Failed to fetch verse range', [
                    'surah' => $surah,
                    'verseStart' => $verseStart,
                    'verseEnd' => $verseEnd,
                    'status' => $response->status(),
                ]);
                throw new \Exception("Failed to fetch verses {$surah}:{$verseStart}-{$verseEnd}");
            }

            $data = $response->json()['data'] ?? [];

            if (isset($data[0])) {
                return array_map(fn($v) => $this->mapVerseData($v, $surah, $verseStart), $data);
            }

            return [];
        });
    }

    /**
     * Map API verse data to normalized array format
     */
    private function mapVerseData(array $data, int $surah, int $verse): array
    {
        return [
            'text' => $data['text'] ?? '',
            'surah_name' => $data['surah']['name'] ?? '',
            'number' => $data['number'] ?? 0,
            'surah' => $data['surah']['number'] ?? $surah,
            'numberInSurah' => $data['numberInSurah'] ?? $verse,
        ];
    }

    public function searchVerse(string $query): array
    {
        $cacheKey = "quran_search_" . md5($query);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query) {
            $response = Http::get(self::BASE_URL . "/search/" . urlencode($query) . "/quran-quranuthmani");

            if ($response->failed()) {
                Log::error('QuranApiService: Search failed', [
                    'query' => $query,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception("Quran search failed for query: {$query}");
            }

            return $response->json()['data']['matches'] ?? [];
        });
    }

    public function getJuzForPage(int $pageNumber): int
    {
        foreach (self::JUZ_PAGE_RANGES as $juz => $range) {
            if ($pageNumber >= $range[0] && $pageNumber <= $range[1]) {
                return $juz;
            }
        }
        return 1;
    }

    public function getJuzOptions(): array
    {
        return [
            1 => 'الجزء الأول',
            2 => 'الجزء الثاني',
            3 => 'الجزء الثالث',
            4 => 'الجزء الرابع',
            5 => 'الجزء الخامس',
            6 => 'الجزء السادس',
            7 => 'الجزء السابع',
            8 => 'الجزء الثامن',
            9 => 'الجزء التاسع',
            10 => 'الجزء العاشر',
            11 => 'الجزء الحادي عشر',
            12 => 'الجزء الثاني عشر',
            13 => 'الجزء الثالث عشر',
            14 => 'الجزء الرابع عشر',
            15 => 'الجزء الخامس عشر',
            16 => 'الجزء السادس عشر',
            17 => 'الجزء السابع عشر',
            18 => 'الجزء الثامن عشر',
            19 => 'الجزء التاسع عشر',
            20 => 'الجزء العشرون',
            21 => 'الجزء الحادي والعشرون',
            22 => 'الجزء الثاني والعشرون',
            23 => 'الجزء الثالث والعشرون',
            24 => 'الجزء الرابع والعشرون',
            25 => 'الجزء الخامس والعشرون',
            26 => 'الجزء السادس والعشرون',
            27 => 'الجزء السابع والعشرون',
            28 => 'الجزء الثامن والعشرون',
            29 => 'الجزء التاسع والعشرون',
            30 => 'الجزء الثلاثون',
        ];
    }

    public function getPartPages(int $partNumber): array
    {
        if ($partNumber < 1 || $partNumber > 30) {
            throw new \InvalidArgumentException("Part number must be between 1 and 30, got {$partNumber}");
        }

        [$start, $count] = self::JUZ_DATA[$partNumber];
        return range($start, $start + $count - 1);
    }

    /**
     * Get the starting page number for a juz
     *
     * @param int $juzNumber
     * @return int
     */
    public function getJuzStartPage(int $juzNumber): int
    {
        if ($juzNumber < 1 || $juzNumber > 30) {
            throw new \InvalidArgumentException("Juz number must be between 1 and 30, got {$juzNumber}");
        }

        return self::JUZ_DATA[$juzNumber][0];
    }

    /**
     * Get page count for a juz
     *
     * @param int $juzNumber
     * @return int
     */
    public function getJuzPageCount(int $juzNumber): int
    {
        if ($juzNumber < 1 || $juzNumber > 30) {
            throw new \InvalidArgumentException("Juz number must be between 1 and 30, got {$juzNumber}");
        }

        return self::JUZ_DATA[$juzNumber][1];
    }
}
