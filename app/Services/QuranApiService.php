<?php

namespace App\Services;

use App\Models\Ayah;
use App\Models\Surah;
use App\Models\Reciter;
use App\Models\AudioSegment;
use App\Models\QuranWord;
use App\Models\TajweedRule;
use App\Models\WordConcordance;
use App\Models\Mutashabihat;
use App\Models\QuranPageLayout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QuranApiService
{
    private const BASE_URL = 'https://api.alquran.cloud/v1';
    private const CACHE_TTL = 60 * 60 * 24 * 30; // 30 days

    /**
     * Hardcoded surah info for client-side dropdowns
     */
    public const SURAH_INFO = [
        1 => ['name' => 'الفاتحة', 'aya' => 7],
        2 => ['name' => 'البقرة', 'aya' => 286],
        3 => ['name' => 'آل عمران', 'aya' => 200],
        4 => ['name' => 'النساء', 'aya' => 176],
        5 => ['name' => 'المائدة', 'aya' => 120],
        6 => ['name' => 'الأنعام', 'aya' => 165],
        7 => ['name' => 'الأعراف', 'aya' => 206],
        8 => ['name' => 'الأنفال', 'aya' => 75],
        9 => ['name' => 'التوبة', 'aya' => 129],
        10 => ['name' => 'يونس', 'aya' => 109],
        11 => ['name' => 'هود', 'aya' => 123],
        12 => ['name' => 'يوسف', 'aya' => 111],
        13 => ['name' => 'الرعد', 'aya' => 43],
        14 => ['name' => 'إبراهيم', 'aya' => 52],
        15 => ['name' => 'الحجر', 'aya' => 99],
        16 => ['name' => 'النحل', 'aya' => 128],
        17 => ['name' => 'الإسراء', 'aya' => 111],
        18 => ['name' => 'الكهف', 'aya' => 110],
        19 => ['name' => 'مريم', 'aya' => 98],
        20 => ['name' => 'طه', 'aya' => 135],
        21 => ['name' => 'الأنبياء', 'aya' => 112],
        22 => ['name' => 'الحج', 'aya' => 78],
        23 => ['name' => 'المؤمنون', 'aya' => 118],
        24 => ['name' => 'النور', 'aya' => 64],
        25 => ['name' => 'الفرقان', 'aya' => 77],
        26 => ['name' => 'الشعراء', 'aya' => 227],
        27 => ['name' => 'النمل', 'aya' => 93],
        28 => ['name' => 'القصص', 'aya' => 88],
        29 => ['name' => 'العنكبوت', 'aya' => 69],
        30 => ['name' => 'الروم', 'aya' => 60],
        31 => ['name' => 'لقمان', 'aya' => 34],
        32 => ['name' => 'السجدة', 'aya' => 30],
        33 => ['name' => 'الأحزاب', 'aya' => 73],
        34 => ['name' => 'سبأ', 'aya' => 54],
        35 => ['name' => 'فاطر', 'aya' => 45],
        36 => ['name' => 'يس', 'aya' => 83],
        37 => ['name' => 'الصافات', 'aya' => 182],
        38 => ['name' => 'ص', 'aya' => 88],
        39 => ['name' => 'الزمر', 'aya' => 75],
        40 => ['name' => 'غافر', 'aya' => 85],
        41 => ['name' => 'فصلت', 'aya' => 54],
        42 => ['name' => 'الشورى', 'aya' => 53],
        43 => ['name' => 'الزخرف', 'aya' => 89],
        44 => ['name' => 'الدخان', 'aya' => 59],
        45 => ['name' => 'الجاثية', 'aya' => 37],
        46 => ['name' => 'الأحقاف', 'aya' => 35],
        47 => ['name' => 'محمد', 'aya' => 38],
        48 => ['name' => 'الفتح', 'aya' => 29],
        49 => ['name' => 'الحجرات', 'aya' => 18],
        50 => ['name' => 'ق', 'aya' => 45],
        51 => ['name' => 'الذاريات', 'aya' => 60],
        52 => ['name' => 'الطور', 'aya' => 49],
        53 => ['name' => 'النجم', 'aya' => 62],
        54 => ['name' => 'القمر', 'aya' => 55],
        55 => ['name' => 'الرحمن', 'aya' => 78],
        56 => ['name' => 'الواقعة', 'aya' => 96],
        57 => ['name' => 'الحديد', 'aya' => 29],
        58 => ['name' => 'المجادلة', 'aya' => 22],
        59 => ['name' => 'الحشر', 'aya' => 24],
        60 => ['name' => 'الممتحنة', 'aya' => 13],
        61 => ['name' => 'الصف', 'aya' => 14],
        62 => ['name' => 'الجمعة', 'aya' => 11],
        63 => ['name' => 'المنافقون', 'aya' => 11],
        64 => ['name' => 'التغابن', 'aya' => 18],
        65 => ['name' => 'الطلاق', 'aya' => 12],
        66 => ['name' => 'التحريم', 'aya' => 12],
        67 => ['name' => 'الملك', 'aya' => 30],
        68 => ['name' => 'القلم', 'aya' => 52],
        69 => ['name' => 'الحاقة', 'aya' => 52],
        70 => ['name' => 'المعارج', 'aya' => 44],
        71 => ['name' => 'نوح', 'aya' => 28],
        72 => ['name' => 'الجن', 'aya' => 28],
        73 => ['name' => 'المزمل', 'aya' => 20],
        74 => ['name' => 'المدثر', 'aya' => 56],
        75 => ['name' => 'القيامة', 'aya' => 40],
        76 => ['name' => 'الإنسان', 'aya' => 31],
        77 => ['name' => 'المرسلات', 'aya' => 50],
        78 => ['name' => 'النبأ', 'aya' => 40],
        79 => ['name' => 'النازعات', 'aya' => 46],
        80 => ['name' => 'عبس', 'aya' => 42],
        81 => ['name' => 'التكوير', 'aya' => 29],
        82 => ['name' => 'الإنفطار', 'aya' => 19],
        83 => ['name' => 'المطففين', 'aya' => 36],
        84 => ['name' => 'الإنشقاق', 'aya' => 25],
        85 => ['name' => 'البروج', 'aya' => 22],
        86 => ['name' => 'الطارق', 'aya' => 17],
        87 => ['name' => 'الأعلى', 'aya' => 19],
        88 => ['name' => 'الغاشية', 'aya' => 26],
        89 => ['name' => 'الفجر', 'aya' => 30],
        90 => ['name' => 'البلد', 'aya' => 20],
        91 => ['name' => 'الشمس', 'aya' => 18],
        92 => ['name' => 'الليل', 'aya' => 21],
        93 => ['name' => 'الضحى', 'aya' => 11],
        94 => ['name' => 'الشرح', 'aya' => 8],
        95 => ['name' => 'التين', 'aya' => 8],
        96 => ['name' => 'العلق', 'aya' => 19],
        97 => ['name' => 'القدر', 'aya' => 5],
        98 => ['name' => 'البينة', 'aya' => 8],
        99 => ['name' => 'الزلزلة', 'aya' => 8],
        100 => ['name' => 'العاديات', 'aya' => 11],
        101 => ['name' => 'القارعة', 'aya' => 11],
        102 => ['name' => 'التكاثر', 'aya' => 8],
        103 => ['name' => 'العصر', 'aya' => 3],
        104 => ['name' => 'الهمزة', 'aya' => 9],
        105 => ['name' => 'الفيل', 'aya' => 5],
        106 => ['name' => 'قريش', 'aya' => 4],
        107 => ['name' => 'الماعون', 'aya' => 7],
        108 => ['name' => 'الكوثر', 'aya' => 3],
        109 => ['name' => 'الكافرون', 'aya' => 6],
        110 => ['name' => 'النصر', 'aya' => 3],
        111 => ['name' => 'المسد', 'aya' => 5],
        112 => ['name' => 'الإخلاص', 'aya' => 4],
        113 => ['name' => 'الفلق', 'aya' => 5],
        114 => ['name' => 'الناس', 'aya' => 6],
    ];

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

    /**
     * Check if local Quran data is available
     */
    public function useLocalData(): bool
    {
        return Ayah::count() > 0;
    }

    /**
     * Strip HTML tags from Quran text (API returns tajweed as HTML which we handle separately)
     */
    private function stripHtmlTags(string $text): string
    {
        return strip_tags($text);
    }

    /**
     * Get Quran page from local DB or API
     */
    public function getPage(int $pageNumber): array
    {
        if ($this->useLocalData()) {
            return $this->getPageFromLocal($pageNumber);
        }
        return $this->getPageFromAPI($pageNumber);
    }

    private function getPageFromLocal(int $pageNumber): array
    {
        $ayahs = Ayah::where('page_number', $pageNumber)
            ->with('surah')
            ->orderBy('id')
            ->get();

        if ($ayahs->isEmpty()) {
            // Fallback to API if local page not found
            return $this->getPageFromAPI($pageNumber);
        }

        $result = [
            'number' => $pageNumber,
            'ayahs' => [],
        ];

        foreach ($ayahs as $ayah) {
            $result['ayahs'][] = [
                'number' => $this->getGlobalAyahNumber($ayah->surah->surah_number, $ayah->ayah_number),
                'text' => $this->stripHtmlTags($ayah->text),
                'numberInSurah' => $ayah->ayah_number,
                'surah' => [
                    'number' => $ayah->surah->surah_number,
                    'name' => $ayah->surah->name_ar,
                ],
            ];
        }

        return $result;
    }

    private function getPageFromAPI(int $pageNumber): array
    {
        $cacheKey = "quran_page_{$pageNumber}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($pageNumber) {
            $response = Http::get(self::BASE_URL . "/page/{$pageNumber}/quran-quranuthmani");

            if ($response->failed()) {
                Log::error('QuranApiService: Failed to fetch page', [
                    'page' => $pageNumber,
                    'status' => $response->status(),
                ]);
                throw new \Exception("Failed to fetch Quran page: {$pageNumber}");
            }

            $data = $response->json()['data'];
            if (isset($data['ayahs'])) {
                foreach ($data['ayahs'] as &$ayah) {
                    if (isset($ayah['text'])) {
                        $ayah['text'] = $this->stripHtmlTags($ayah['text']);
                    }
                }
                unset($ayah);
            }

            return $data;
        });
    }

    /**
     * Get a specific verse from local DB or API
     */
    public function getVerse(int $surah, int $verse): array
    {
        if ($this->useLocalData()) {
            $ayah = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $surah))
                ->where('ayah_number', $verse)
                ->with('surah')
                ->first();

            if ($ayah) {
                return [
                    'text' => $this->stripHtmlTags($ayah->text),
                    'surah_name' => $ayah->surah->name_ar,
                    'number' => $this->getGlobalAyahNumber($surah, $verse),
                    'surah' => $ayah->surah->surah_number,
                    'numberInSurah' => $verse,
                ];
            }
        }

        return $this->getVerseFromAPI($surah, $verse);
    }

    private function getVerseFromAPI(int $surah, int $verse): array
    {
        $cacheKey = "quran_verse_{$surah}_{$verse}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($surah, $verse) {
            $response = Http::get(self::BASE_URL . "/ayah/{$surah}:{$verse}/quran-quranuthmani");

            if ($response->failed()) {
                throw new \Exception("Failed to fetch verse {$surah}:{$verse}");
            }

            $data = $response->json()['data'] ?? [];
            return [
                'text' => $this->stripHtmlTags($data['text'] ?? ''),
                'surah_name' => $data['surah']['name'] ?? '',
                'number' => $data['number'] ?? 0,
                'surah' => $data['surah']['number'] ?? $surah,
                'numberInSurah' => $data['numberInSurah'] ?? $verse,
            ];
        });
    }

    /**
     * Get tafseer (interpretation) for a verse from open sources
     */
    public function getTafseer(string $verseKey, string $interpretation = 'ibn_kathir'): array
    {
        $cacheKey = "tafseer_{$verseKey}_{$interpretation}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($verseKey, $interpretation) {
            $response = Http::get(self::BASE_URL . "/tafseer/{$verseKey}", [
                'edition' => $interpretation,
            ]);

            if ($response->failed()) {
                Log::warning("Tafseer API failed for {$verseKey}", ['status' => $response->status()]);
                return [
                    'source' => $this->getTafseerSourceName($interpretation),
                    'text' => ['التفسير غير متاح حالياً'],
                ];
            }

            $data = $response->json()['data'] ?? [];
            return [
                'source' => $data['edition']['name'] ?? $this->getTafseerSourceName($interpretation),
                'text' => $this->extractTafseerText($data),
            ];
        });
    }

    private function getTafseerSourceName(string $interpretation): string
    {
        return match ($interpretation) {
            'ar.tafseer_ibn_kathir' => 'تفسير ابن كثير',
            'ar.tafseer_saadi' => 'تيسير الكريم الرحمن',
            'ar.tafseer_tabari' => 'جامع البيان',
            default => 'التفسير',
        };
    }

    private function extractTafseerText(array $data): array
    {
        $texts = [];
        if (isset($data['text'])) {
            $texts[] = $data['text'];
        }
        if (isset($data['ayahs']) && is_array($data['ayahs'])) {
            foreach ($data['ayahs'] as $ayah) {
                if (isset($ayah['text'])) {
                    $texts[] = $ayah['text'];
                }
            }
        }
        return array_filter($texts);
    }

    /**
     * Get translations for a verse from open sources
     */
    public function getTranslation(string $verseKey): array
    {
        $cacheKey = "translation_{$verseKey}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($verseKey) {
            $translations = [];

            $editionMap = [
                'en' => 'en.sahih',
                'ur' => 'ur.ahmedali',
                'id' => 'id.indonesian',
            ];

            foreach ($editionMap as $lang => $edition) {
                try {
                    $response = Http::get(self::BASE_URL . "/ayah/{$verseKey}", [
                        'edition' => $edition,
                    ]);
                    if (!$response->failed()) {
                        $data = $response->json()['data'] ?? [];
                        if (isset($data['text'])) {
                            $translations[$lang] = $data['text'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Translation fetch failed for {$verseKey}", [
                        'lang' => $lang,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $translations;
        });
    }

    /**
     * Get range of verses
     */
    public function getVerses(int $surah, int $verseStart, int $verseEnd): array
    {
        if ($this->useLocalData()) {
            return $this->getVersesFromLocal($surah, $verseStart, $verseEnd);
        }
        return $this->getVersesFromAPI($surah, $verseStart, $verseEnd);
    }

    private function getVersesFromLocal(int $surah, int $verseStart, int $verseEnd): array
    {
        $ayahs = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $surah))
            ->whereBetween('ayah_number', [$verseStart, $verseEnd])
            ->with('surah')
            ->orderBy('ayah_number')
            ->get();

        if ($ayahs->isEmpty()) {
            return $this->getVersesFromAPI($surah, $verseStart, $verseEnd);
        }

        $result = [];
        foreach ($ayahs as $ayah) {
            $result[] = [
                'text' => $this->stripHtmlTags($ayah->text),
                'surah_name' => $ayah->surah->name_ar,
                'number' => $this->getGlobalAyahNumber($surah, $ayah->ayah_number),
                'surah' => $surah,
                'numberInSurah' => $ayah->ayah_number,
            ];
        }

        return $result;
    }

    private function getVersesFromAPI(int $surah, int $verseStart, int $verseEnd): array
    {
        $cacheKey = "quran_verses_{$surah}_{$verseStart}_{$verseEnd}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($surah, $verseStart, $verseEnd) {
            $response = Http::get(self::BASE_URL . "/surah/{$surah}/quran-quranuthmani");

            if ($response->failed()) {
                throw new \Exception("Failed to fetch surah {$surah}");
            }

            $data = $response->json()['data'] ?? [];
            $allVerses = $data['ayahs'] ?? [];
            $verses = [];

            for ($i = $verseStart - 1; $i < $verseEnd && $i < count($allVerses); $i++) {
                $verse = $allVerses[$i];
                $verses[] = [
                    'text' => $this->stripHtmlTags($verse['text']),
                    'surah_name' => $verse['surah']['name'] ?? '',
                    'number' => $verse['number'] ?? 0,
                    'surah' => $verse['surah']['number'] ?? $surah,
                    'numberInSurah' => $verse['numberInSurah'] ?? ($i + 1),
                ];
            }

            return $verses;
        });
    }

    /**
     * Get audio URLs for a page using local segments or fallback to CDN
     */
    public function getAudioUrls(int $pageNumber, ?string $reciterSlug = null): array
    {
        $reciter = $this->getReciter($reciterSlug);

        if (!$reciter) {
            return $this->getAudioUrlsFromCDN($pageNumber);
        }

        $ayahs = Ayah::where('page_number', $pageNumber)
            ->with('surah')
            ->get();

        $urls = [];
        foreach ($ayahs as $ayah) {
            $surahNum = $ayah->surah->surah_number;
            $verseNum = $ayah->ayah_number;
            $key = "{$surahNum}:{$verseNum}";

            // Try local segments first
            $segment = AudioSegment::where('reciter_id', $reciter->id)
                ->where('surah_number', $surahNum)
                ->where('ayah_number', $verseNum)
                ->whereNull('word_position') // full ayah
                ->first();

            if ($segment && $segment->audio_url) {
                $urls[$key] = $segment->audio_url;
            } else {
                $urls[$key] = $this->getVerseAudioUrl($this->getGlobalAyahNumber($surahNum, $verseNum));
            }
        }

        return $urls;
    }

    private function getAudioUrlsFromCDN(int $pageNumber): array
    {
        $cacheKey = "quran_audio_{$pageNumber}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($pageNumber) {
            $pageData = $this->getPage($pageNumber);
            $urls = [];

            foreach ($pageData['ayahs'] as $verse) {
                $surah = $verse['surah']['number'];
                $verseInSurah = $verse['numberInSurah'];
                $verseKey = "{$surah}:{$verseInSurah}";
                $urls[$verseKey] = "https://cdn.islamic.network/quran/audio/128/ar.alafasy/{$verse['number']}.mp3";
            }

            return $urls;
        });
    }

    /**
     * Get audio URL for a specific verse
     */
    public function getVerseAudioUrl(int $verseNumber, ?string $reciterSlug = null): string
    {
        $reciter = $this->getReciter($reciterSlug);

        if ($reciter && $reciter->audio_base_url) {
            return "{$reciter->audio_base_url}/{$verseNumber}.mp3";
        }

        return "https://cdn.islamic.network/quran/audio/128/ar.alafasy/{$verseNumber}.mp3";
    }

    /**
     * Get word-level audio segments for a verse (for word highlighting)
     */
    public function getWordSegments(int $surah, int $ayah, ?string $reciterSlug = null): array
    {
        $reciter = $this->getReciter($reciterSlug);

        if (!$reciter || !$reciter->is_segmented) {
            return [];
        }

        return AudioSegment::where('reciter_id', $reciter->id)
            ->where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->whereNotNull('word_position')
            ->orderBy('word_position')
            ->get()
            ->map(fn($seg) => [
                'word_position' => $seg->word_position,
                'start_time' => (float) $seg->start_time,
                'end_time' => (float) $seg->end_time,
                'audio_url' => $seg->audio_url,
            ])
            ->toArray();
    }

    /**
     * Get words for a verse (word-by-word)
     */
    public function getVerseWords(int $surah, int $ayah): array
    {
        $words = QuranWord::where('surah_number', $surah)
            ->where('ayah_number', $ayah)
            ->orderBy('word_position')
            ->get();

        if ($words->isEmpty()) {
            // Fallback: split ayah text from local DB
            $ayah = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $surah))
                ->where('ayah_number', $ayah)
                ->first();

            if (!$ayah) return [];

            $cleanText = preg_replace('/[\x{2000}-\x{200F}\x{2028}\x{2029}\x{205F}\x{2060}\x{FEFF}]/u', '', trim($ayah->text));
            $parts = preg_split('/\s+/', $cleanText);
            return array_map(fn($text, $i) => [
                'word_position' => $i + 1,
                'text_uthmani' => $text,
                'text_imlaei' => null,
            ], $parts, array_keys($parts));
        }

        return $words->map(fn($w) => [
            'word_position' => $w->word_position,
            'text_uthmani' => $w->text_uthmani,
            'text_imlaei' => $w->text_imlaei,
        ])->toArray();
    }

    /**
     * Get Tajweed rules for a verse
     */
    public function getTajweedForVerse(int $surah, int $ayah): array
    {
        $rules = TajweedRule::getForVerse($surah, $ayah);

        if (!empty($rules)) {
            return $rules;
        }

        // Fallback: generate basic tajweed hints from text patterns
        return $this->generateBasicTajweed($surah, $ayah);
    }

    /**
     * Generate basic Tajweed rules from text patterns
     */
    private function generateBasicTajweed(int $surah, int $ayah): array
    {
        $ayahText = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $surah))
            ->where('ayah_number', $ayah)
            ->first();

        if (!$ayahText) return [];

        $text = $ayahText->text;
        $rules = [];
        $colors = TajweedRule::getDefaultColors();
        $labels = TajweedRule::getRuleLabels();

        // Simple pattern matching for common tajweed rules
        $patterns = [
            'madd_2' => '/[\x{064E}\x{064F}\x{0650}\x{0652}][\x{0627}\x{0649}\x{0648}]/u',
            'qalqalah' => '/[قطبجد](?=[\s\x{064B}-\x{065F}\x{0670}]|$)/u',
            'ikhfa' => '/[تثجدقك](?=[\x{064B}\x{064C}\x{064D}\x{064E}\x{064F}\x{0650}\x{0651}\x{0652}])/u',
        ];

        foreach ($patterns as $ruleType => $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $startPos = $this->utf8ByteOffsetToCharIndex($text, $match[1]);
                    $rules[] = [
                        'word_position' => null,
                        'rule_type' => $ruleType,
                        'start_char' => $startPos,
                        'end_char' => $startPos + mb_strlen($match[0]) - 1,
                        'rule_color' => $colors[$ruleType] ?? '#666666',
                    ];
                }
            }
        }

        return $rules;
    }

    private function utf8ByteOffsetToCharIndex(string $text, int $byteOffset): int
    {
        return mb_strlen(substr($text, 0, $byteOffset));
    }

    /**
     * Get Mushaf page layout data
     */
    public function getPageLayout(int $pageNumber, string $layoutName = 'KFGQPC_V4'): array
    {
        if (QuranPageLayout::hasLayout($layoutName)) {
            $layout = QuranPageLayout::getPageLayout($layoutName, $pageNumber);
            if (!empty($layout)) {
                return [
                    'layout_name' => $layoutName,
                    'page_number' => $pageNumber,
                    'lines' => $this->groupLayoutByLines($layout),
                ];
            }
        }

        // Fallback: generate simple line-by-line from ayah texts
        return $this->generateFallbackLayout($pageNumber);
    }

    private function groupLayoutByLines(array $layout): array
    {
        $lines = [];
        foreach ($layout as $item) {
            $lineNum = $item['line_number'];
            if (!isset($lines[$lineNum])) {
                $lines[$lineNum] = [];
            }
            $lines[$lineNum][] = $item;
        }
        return $lines;
    }

    private function generateFallbackLayout(int $pageNumber): array
    {
        $ayahs = Ayah::where('page_number', $pageNumber)
            ->with('surah')
            ->orderBy('id')
            ->get();

        $lines = [];
        $currentLine = 1;

        foreach ($ayahs as $ayah) {
            $lines[$currentLine] = [
                [
                    'surah_number' => $ayah->surah->surah_number,
                    'ayah_number' => $ayah->ayah_number,
                    'text' => $ayah->text,
                    'line_number' => $currentLine,
                    'position_in_line' => 1,
                ]
            ];
            $currentLine++;
        }

        return [
            'layout_name' => 'fallback',
            'page_number' => $pageNumber,
            'lines' => $lines,
        ];
    }

    /**
     * Get available Mushaf layouts
     */
    public function getAvailableLayouts(): array
    {
        $layouts = QuranPageLayout::getAvailableLayouts();
        if (!empty($layouts)) {
            return $layouts;
        }
        return ['KFGQPC_V4' => 'مصحف المدينة', 'Indopak_15' => 'هندوباك ١٥ سطر'];
    }

    /**
     * Get word concordance (morphology) for a verse
     */
    public function getWordConcordance(int $surah, int $ayah): array
    {
        $data = WordConcordance::getForVerse($surah, $ayah);
        if (!empty($data)) {
            return $data;
        }

        // Fallback: generate basic POS tags from common patterns
        return $this->generateBasicConcordance($surah, $ayah);
    }

    /**
     * Get concordance for a specific word
     */
    public function getConcordanceForWord(int $surah, int $ayah, int $wordPosition): ?array
    {
        $concordance = WordConcordance::getForWord($surah, $ayah, $wordPosition);
        if ($concordance) {
            return $concordance->toArray();
        }
        return null;
    }

    /**
     * Generate basic concordance from text patterns
     */
    private function generateBasicConcordance(int $surah, int $ayah): array
    {
        $ayahText = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $surah))
            ->where('ayah_number', $ayah)
            ->first();

        if (!$ayahText) return [];

        $words = preg_split('/\s+/', trim($ayahText->text));
        $result = [];
        $posLabels = WordConcordance::posLabels();

        foreach ($words as $i => $word) {
            $pos = $this->detectBasicPOS($word);
            $result[] = [
                'word_position' => $i + 1,
                'word_text' => $word,
                'part_of_speech' => $pos,
                'pos_label' => $posLabels[$pos] ?? $pos,
                'root' => null,
                'morphology' => null,
                'translation' => null,
            ];
        }

        return $result;
    }

    private function detectBasicPOS(string $word): string
    {
        // Simple heuristic-based POS detection
        $particles = ['في', 'من', 'إلى', 'على', 'عن', 'مع', 'حتى', 'منذ', 'خلا', 'عدا', 'حاشا', 'ليس', 'ما', 'لا', 'لم', 'لن', 'إن', 'أن', 'كي', 'لعل', 'لو', 'إذ', 'ذا', 'ذو', 'ذات', 'أي', 'هذا', 'هذه', 'ذلك', 'تلك', 'هؤلاء', 'أولئك', 'حيث', 'كيف', 'متى', 'كم', 'أين', 'أي', 'هل'];
        $pronouns = ['هو', 'هي', 'هم', 'هن', 'أنت', 'أنتِ', 'أنتم', 'أنتن', 'أنا', 'نحن', 'إياك', 'إياها', 'إياهم', 'إياهما', 'إياكن'];
        $verbs = ['قال', 'قالت', 'كان', 'كانت', 'يكون', 'كانوا', 'فعل', 'فعلت', 'يفعل', 'فعلوا', 'جاء', 'جاءت', 'يجيء', 'جاؤوا', 'أتى', 'آت', 'يأتي'];

        $clean = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $word);

        if (in_array($clean, $particles, true)) return 'particle';
        if (in_array($clean, $pronouns, true)) return 'pronoun';
        if (in_array($clean, $verbs, true)) return 'verb';
        if (str_starts_with($word, 'ال')) return 'noun';
        if (preg_match('/[اوي]$/u', $word)) return 'noun';

        return 'noun';
    }

    /**
     * Get mutashabihat (similar verses) for a verse
     */
    public function getMutashabihat(int $surah, int $ayah): array
    {
        return Mutashabihat::getForVerse($surah, $ayah);
    }

    /**
     * Get mutashabihat for all verses in a range
     */
    public function getMutashabihatForRange(array $verses): array
    {
        $result = [];
        foreach ($verses as $verse) {
            $key = "{$verse['surah']}:{$verse['verse_number']}";
            $similar = Mutashabihat::getForVerse($verse['surah'], $verse['verse_number']);
            if (!empty($similar)) {
                $result[$key] = $similar;
            }
        }
        return $result;
    }

    /**
     * Get available reciters from DB
     */
    public function getAvailableReciters(): array
    {
        $reciters = Reciter::where('is_active', true)->get();

        if ($reciters->isNotEmpty()) {
            return $reciters->pluck('name', 'slug')->toArray();
        }

        // Fallback to hardcoded list
        return [
            'alafasy' => 'مشاري راشد العفاسي',
            'abdulbaset' => 'عبد الباسط عبد الصمد',
            'minshawimujawwad' => 'محمد صدقي المنشاوي',
            'husary' => 'محمود خليل الحصري',
            'shuraym' => 'سعود الشريم',
            'ahmedajamy' => 'أحمد بن علي العجمي',
            'sudais' => 'عبد الرحمن السديس',
            'saad' => 'سعد الغامدي',
            'fares' => 'فارس عباد',
        ];
    }

    /**
     * Get reciter by slug
     */
    private function getReciter(?string $slug): ?Reciter
    {
        if (!$slug) {
            return Reciter::where('is_active', true)->first();
        }
        return Reciter::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Search for verses
     */
    public function searchVerse(string $query): array
    {
        $cacheKey = "quran_search_" . md5($query);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query) {
            $response = Http::get(self::BASE_URL . "/search/" . urlencode($query));

            if ($response->failed()) {
                Log::error('QuranApiService: Search failed', [
                    'query' => $query,
                    'status' => $response->status(),
                ]);
                throw new \Exception("Quran search failed for query: {$query}");
            }

            $json = $response->json();
            if (($json['code'] ?? 0) == 404) {
                return [];
            }

            $matches = $json['data']['matches'] ?? [];

            // Fetch actual verse texts with Uthmani edition
            $results = [];
            foreach ($matches as $match) {
                $surah = $match['surah'] ?? [];
                $surahNum = $surah['number'] ?? 0;
                $verseNum = $match['numberInSurah'] ?? 0;
                if ($surahNum && $verseNum) {
                    $verseKey = "$surahNum:$verseNum";
                    $verseData = $this->getVerseText($verseKey);
                    $results[] = [
                        'surah' => $surahNum,
                        'surah_name' => $surah['name'] ?? $this->getSurahName($surahNum),
                        'verse' => $verseNum,
                        'text' => $verseData,
                    ];
                }
            }

            return $results;
        });
    }

    private function getVerseText(string $verseKey): string
    {
        try {
            $response = Http::get(self::BASE_URL . "/ayah/" . $verseKey . "/quran-uthmani");
            if ($response->successful()) {
                return $response->json()['data']['text'] ?? '';
            }
        } catch (\Exception $e) {
            Log::warning('QuranApiService: Failed to get verse text', ['key' => $verseKey, 'error' => $e->getMessage()]);
        }
        return '';
    }

    // Juz helpers
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
        $options = [];
        for ($i = 1; $i <= 30; $i++) {
            $options[$i] = 'الجزء ' . $this->arabicOrdinal($i);
        }
        return $options;
    }

    private function arabicOrdinal(int $num): string
    {
        $ordinals = [
            1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث', 4 => 'الرابع', 5 => 'الخامس',
            6 => 'السادس', 7 => 'السابع', 8 => 'الثامن', 9 => 'التاسع', 10 => 'العاشر',
            11 => 'الحادي عشر', 12 => 'الثاني عشر', 13 => 'الثالث عشر', 14 => 'الرابع عشر',
            15 => 'الخامس عشر', 16 => 'السادس عشر', 17 => 'السابع عشر', 18 => 'الثامن عشر',
            19 => 'التاسع عشر', 20 => 'العشرون', 21 => 'الحادي والعشرون',
            22 => 'الثاني والعشرون', 23 => 'الثالث والعشرون', 24 => 'الرابع والعشرون',
            25 => 'الخامس والعشرون', 26 => 'السادس والعشرون', 27 => 'السابع والعشرون',
            28 => 'الثامن والعشرون', 29 => 'التاسع والعشرون', 30 => 'الثلاثون',
        ];
        return $ordinals[$num] ?? (string) $num;
    }

    public function getPartPages(int $partNumber): array
    {
        if ($partNumber < 1 || $partNumber > 30) {
            throw new \InvalidArgumentException("Part number must be between 1 and 30");
        }

        [$start, $count] = self::JUZ_DATA[$partNumber];
        return range($start, $start + $count - 1);
    }

    public function getJuzStartPage(int $juzNumber): int
    {
        if ($juzNumber < 1 || $juzNumber > 30) {
            throw new \InvalidArgumentException("Juz number must be between 1 and 30");
        }
        return self::JUZ_DATA[$juzNumber][0];
    }

    public function getJuzPageCount(int $juzNumber): int
    {
        if ($juzNumber < 1 || $juzNumber > 30) {
            throw new \InvalidArgumentException("Juz number must be between 1 and 30");
        }
        return self::JUZ_DATA[$juzNumber][1];
    }

    public function getSurahs(): array
    {
        if ($this->useLocalData()) {
            return Surah::orderBy('surah_number')
                ->get()
                ->mapWithKeys(fn($s) => [
                    $s->surah_number => [
                        'name' => $s->name_ar,
                        'aya' => $s->total_ayahs,
                        'type' => null,
                    ]
                ])
                ->toArray();
        }

        // Fallback to hardcoded
        return $this->getHardcodedSurahs();
    }

    public function getSurah(int $number): array
    {
        if ($this->useLocalData()) {
            $surah = Surah::where('surah_number', $number)->first();
            if ($surah) {
                return [
                    'number' => $surah->surah_number,
                    'name' => $surah->name_ar,
                    'ayahs' => Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $number))
                        ->orderBy('ayah_number')
                        ->get()
                        ->map(fn($a) => [
                            'number' => $this->getGlobalAyahNumber($number, $a->ayah_number),
                            'text' => $this->stripHtmlTags($a->text),
                            'numberInSurah' => $a->ayah_number,
                            'surah' => ['number' => $number, 'name' => $surah->name_ar],
                        ])
                        ->toArray(),
                ];
            }
        }

        $cacheKey = "quran_surah_{$number}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($number) {
            $response = Http::get(self::BASE_URL . "/surah/{$number}/quran-quranuthmani");
            if ($response->failed()) {
                throw new \Exception("Failed to fetch Surah {$number}");
            }
            $data = $response->json()['data'];
            if (isset($data['ayahs'])) {
                foreach ($data['ayahs'] as &$ayah) {
                    if (isset($ayah['text'])) {
                        $ayah['text'] = $this->stripHtmlTags($ayah['text']);
                    }
                }
                unset($ayah);
            }
            return $data;
        });
    }

    public function getSurahAudioUrls(int $surahNumber, ?string $reciterSlug = null): array
    {
        $reciter = $this->getReciter($reciterSlug);
        $baseUrl = $reciter?->audio_base_url ?? 'https://cdn.islamic.network/quran/audio/128/ar.alafasy';

        $urls = [];
        $surah = $this->getSurah($surahNumber);

        foreach ($surah['ayahs'] as $verse) {
            $verseInSurah = $verse['numberInSurah'];
            $verseKey = "{$surahNumber}:{$verseInSurah}";
            $urls[$verseKey] = "{$baseUrl}/{$verse['number']}.mp3";
        }

        return $urls;
    }

    /**
     * Calculate approximate global ayah number
     */
    private function getGlobalAyahNumber(int $surah, int $ayah): int
    {
        $count = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', '<', $surah))->count();
        return $count + $ayah;
    }

    private function getHardcodedSurahs(): array
    {
        return [
            1 => ['name' => 'الفاتحة', 'aya' => 7, 'type' => 'مكية'],
            2 => ['name' => 'البقرة', 'aya' => 286, 'type' => 'مدنية'],
            3 => ['name' => 'آل عمران', 'aya' => 200, 'type' => 'مدنية'],
            4 => ['name' => 'النساء', 'aya' => 176, 'type' => 'مدنية'],
            5 => ['name' => 'المائدة', 'aya' => 120, 'type' => 'مدنية'],
            6 => ['name' => 'الأنعام', 'aya' => 165, 'type' => 'مكية'],
            7 => ['name' => 'الأعراف', 'aya' => 206, 'type' => 'مكية'],
            8 => ['name' => 'الأنفال', 'aya' => 75, 'type' => 'مدنية'],
            9 => ['name' => 'التوبة', 'aya' => 129, 'type' => 'مدنية'],
            10 => ['name' => 'يونس', 'aya' => 109, 'type' => 'مكية'],
            11 => ['name' => 'هود', 'aya' => 123, 'type' => 'مكية'],
            12 => ['name' => 'يوسف', 'aya' => 111, 'type' => 'مكية'],
            13 => ['name' => 'الرعد', 'aya' => 43, 'type' => 'مدنية'],
            14 => ['name' => 'إبراهيم', 'aya' => 52, 'type' => 'مكية'],
            15 => ['name' => 'الحجر', 'aya' => 99, 'type' => 'مكية'],
            16 => ['name' => 'النحل', 'aya' => 128, 'type' => 'مكية'],
            17 => ['name' => 'الإسراء', 'aya' => 111, 'type' => 'مكية'],
            18 => ['name' => 'الكهف', 'aya' => 110, 'type' => 'مكية'],
            19 => ['name' => 'مريم', 'aya' => 98, 'type' => 'مكية'],
            20 => ['name' => 'طه', 'aya' => 135, 'type' => 'مكية'],
            21 => ['name' => 'الأنبياء', 'aya' => 112, 'type' => 'مكية'],
            22 => ['name' => 'الحج', 'aya' => 78, 'type' => 'مدنية'],
            23 => ['name' => 'المؤمنون', 'aya' => 118, 'type' => 'مكية'],
            24 => ['name' => 'النور', 'aya' => 64, 'type' => 'مدنية'],
            25 => ['name' => 'الفرقان', 'aya' => 77, 'type' => 'مكية'],
            26 => ['name' => 'الشعراء', 'aya' => 227, 'type' => 'مكية'],
            27 => ['name' => 'النمل', 'aya' => 93, 'type' => 'مكية'],
            28 => ['name' => 'القصص', 'aya' => 88, 'type' => 'مكية'],
            29 => ['name' => 'العنكبوت', 'aya' => 69, 'type' => 'مكية'],
            30 => ['name' => 'الروم', 'aya' => 60, 'type' => 'مكية'],
            31 => ['name' => 'لقمان', 'aya' => 34, 'type' => 'مكية'],
            32 => ['name' => 'السجدة', 'aya' => 30, 'type' => 'مكية'],
            33 => ['name' => 'الأحزاب', 'aya' => 73, 'type' => 'مدنية'],
            34 => ['name' => 'سبأ', 'aya' => 54, 'type' => 'مكية'],
            35 => ['name' => 'فاطر', 'aya' => 45, 'type' => 'مكية'],
            36 => ['name' => 'يس', 'aya' => 83, 'type' => 'مكية'],
            37 => ['name' => 'الصافات', 'aya' => 182, 'type' => 'مكية'],
            38 => ['name' => 'ص', 'aya' => 88, 'type' => 'مكية'],
            39 => ['name' => 'الزمر', 'aya' => 75, 'type' => 'مكية'],
            40 => ['name' => 'غافر', 'aya' => 85, 'type' => 'مكية'],
            41 => ['name' => 'فصلت', 'aya' => 54, 'type' => 'مكية'],
            42 => ['name' => 'الشورى', 'aya' => 53, 'type' => 'مكية'],
            43 => ['name' => 'الزخرف', 'aya' => 89, 'type' => 'مكية'],
            44 => ['name' => 'الدخان', 'aya' => 59, 'type' => 'مكية'],
            45 => ['name' => 'الجاثية', 'aya' => 37, 'type' => 'مكية'],
            46 => ['name' => 'الأحقاف', 'aya' => 35, 'type' => 'مكية'],
            47 => ['name' => 'محمد', 'aya' => 38, 'type' => 'مدنية'],
            48 => ['name' => 'الفتح', 'aya' => 29, 'type' => 'مدنية'],
            49 => ['name' => 'الحجرات', 'aya' => 18, 'type' => 'مدنية'],
            50 => ['name' => 'ق', 'aya' => 45, 'type' => 'مكية'],
            51 => ['name' => 'الذاريات', 'aya' => 60, 'type' => 'مكية'],
            52 => ['name' => 'الطور', 'aya' => 49, 'type' => 'مكية'],
            53 => ['name' => 'النجم', 'aya' => 62, 'type' => 'مكية'],
            54 => ['name' => 'القمر', 'aya' => 55, 'type' => 'مكية'],
            55 => ['name' => 'الرحمن', 'aya' => 78, 'type' => 'مدنية'],
            56 => ['name' => 'الواقعة', 'aya' => 96, 'type' => 'مكية'],
            57 => ['name' => 'الحديد', 'aya' => 29, 'type' => 'مدنية'],
            58 => ['name' => 'المجادلة', 'aya' => 22, 'type' => 'مدنية'],
            59 => ['name' => 'الحشر', 'aya' => 24, 'type' => 'مدنية'],
            60 => ['name' => 'الممتحنة', 'aya' => 13, 'type' => 'مدنية'],
            61 => ['name' => 'الصف', 'aya' => 14, 'type' => 'مدنية'],
            62 => ['name' => 'الجمعة', 'aya' => 11, 'type' => 'مدنية'],
            63 => ['name' => 'المنافقون', 'aya' => 11, 'type' => 'مدنية'],
            64 => ['name' => 'التغابن', 'aya' => 18, 'type' => 'مدنية'],
            65 => ['name' => 'الطلاق', 'aya' => 12, 'type' => 'مدنية'],
            66 => ['name' => 'التحريم', 'aya' => 12, 'type' => 'مدنية'],
            67 => ['name' => 'الملك', 'aya' => 30, 'type' => 'مكية'],
            68 => ['name' => 'القلم', 'aya' => 52, 'type' => 'مكية'],
            69 => ['name' => 'الحاقة', 'aya' => 52, 'type' => 'مكية'],
            70 => ['name' => 'المعارج', 'aya' => 44, 'type' => 'مكية'],
            71 => ['name' => 'نوح', 'aya' => 28, 'type' => 'مكية'],
            72 => ['name' => 'الجن', 'aya' => 28, 'type' => 'مكية'],
            73 => ['name' => 'المزمل', 'aya' => 20, 'type' => 'مكية'],
            74 => ['name' => 'المدثر', 'aya' => 56, 'type' => 'مكية'],
            75 => ['name' => 'القيامة', 'aya' => 40, 'type' => 'مكية'],
            76 => ['name' => 'الإنسان', 'aya' => 31, 'type' => 'مدنية'],
            77 => ['name' => 'المرسلات', 'aya' => 50, 'type' => 'مكية'],
            78 => ['name' => 'النبأ', 'aya' => 40, 'type' => 'مكية'],
            79 => ['name' => 'النازعات', 'aya' => 46, 'type' => 'مكية'],
            80 => ['name' => 'عبس', 'aya' => 42, 'type' => 'مكية'],
            81 => ['name' => 'التكوير', 'aya' => 29, 'type' => 'مكية'],
            82 => ['name' => 'الإنفطار', 'aya' => 19, 'type' => 'مكية'],
            83 => ['name' => 'المطففين', 'aya' => 36, 'type' => 'مكية'],
            84 => ['name' => 'الإنشقاق', 'aya' => 25, 'type' => 'مكية'],
            85 => ['name' => 'البروج', 'aya' => 22, 'type' => 'مكية'],
            86 => ['name' => 'الطارق', 'aya' => 17, 'type' => 'مكية'],
            87 => ['name' => 'الأعلى', 'aya' => 19, 'type' => 'مكية'],
            88 => ['name' => 'الغاشية', 'aya' => 26, 'type' => 'مكية'],
            89 => ['name' => 'الفجر', 'aya' => 30, 'type' => 'مكية'],
            90 => ['name' => 'البلد', 'aya' => 20, 'type' => 'مكية'],
            91 => ['name' => 'الشمس', 'aya' => 18, 'type' => 'مكية'],
            92 => ['name' => 'الليل', 'aya' => 21, 'type' => 'مكية'],
            93 => ['name' => 'الضحى', 'aya' => 11, 'type' => 'مكية'],
            94 => ['name' => 'الشرح', 'aya' => 8, 'type' => 'مكية'],
            95 => ['name' => 'التين', 'aya' => 8, 'type' => 'مكية'],
            96 => ['name' => 'العلق', 'aya' => 19, 'type' => 'مكية'],
            97 => ['name' => 'القدر', 'aya' => 5, 'type' => 'مكية'],
            98 => ['name' => 'البينة', 'aya' => 8, 'type' => 'مدنية'],
            99 => ['name' => 'الزلزلة', 'aya' => 8, 'type' => 'مدنية'],
            100 => ['name' => 'العاديات', 'aya' => 11, 'type' => 'مكية'],
            101 => ['name' => 'القارعة', 'aya' => 11, 'type' => 'مكية'],
            102 => ['name' => 'التكاثر', 'aya' => 8, 'type' => 'مكية'],
            103 => ['name' => 'العصر', 'aya' => 3, 'type' => 'مكية'],
            104 => ['name' => 'الهمزة', 'aya' => 9, 'type' => 'مكية'],
            105 => ['name' => 'الفيل', 'aya' => 5, 'type' => 'مكية'],
            106 => ['name' => 'قريش', 'aya' => 4, 'type' => 'مكية'],
            107 => ['name' => 'الماعون', 'aya' => 7, 'type' => 'مكية'],
            108 => ['name' => 'الكوثر', 'aya' => 3, 'type' => 'مكية'],
            109 => ['name' => 'الكافرون', 'aya' => 6, 'type' => 'مكية'],
            110 => ['name' => 'النصر', 'aya' => 3, 'type' => 'مدنية'],
            111 => ['name' => 'المسد', 'aya' => 5, 'type' => 'مكية'],
            112 => ['name' => 'الإخلاص', 'aya' => 4, 'type' => 'مكية'],
            113 => ['name' => 'الفلق', 'aya' => 5, 'type' => 'مكية'],
            114 => ['name' => 'الناس', 'aya' => 6, 'type' => 'مكية'],
        ];
    }
}
