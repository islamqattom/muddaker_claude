<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reciter;
use App\Models\QuranWord;
use App\Models\AudioSegment;
use App\Models\Surah;
use App\Models\Ayah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportQULData extends Command
{
    protected $signature = 'qul:import
                            {--words= : Path to QUL word-by-word JSON file}
                            {--segments= : Path to QUL audio segments JSON file}
                            {--reciter=alafasy : Reciter slug}
                            {--reciter-name=مشاري راشد العفاسي : Reciter name}
                            {--base-url= : Base URL for audio files}
                            {--seed-quran : Also seed surahs and ayahs from API if empty}';

    protected $description = 'Import Quranic data from QUL (Quranic Universal Library) resources';

    public function handle(): int
    {
        $this->info('بدء استيراد بيانات QUL...');

        try {
            // Step 1: Seed Quran text if needed
            if ($this->option('seed-quran') || Surah::count() === 0) {
                $this->seedQuranFromAPI();
            }

            // Step 2: Import word-by-word data
            $wordsPath = $this->option('words');
            if ($wordsPath && file_exists($wordsPath)) {
                $this->importWords($wordsPath);
            } else {
                $this->warn('ملف الكلمات غير موجود. يمكنك تحميله من: https://qul.tarteel.ai/resources/quran-script');
            }

            // Step 3: Setup reciter
            $reciter = $this->setupReciter();

            // Step 4: Import audio segments
            $segmentsPath = $this->option('segments');
            if ($segmentsPath && file_exists($segmentsPath)) {
                $this->importSegments($segmentsPath, $reciter->id);
            } else {
                $this->warn('ملف المقاطع الصوتية غير موجود. يمكنك تحميله من: https://qul.tarteel.ai/resources/recitation');
                // Fallback: create ayah-level segments from CDN
                $this->createFallbackSegments($reciter->id);
            }

            $this->info('تم استيراد البيانات بنجاح!');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('حدث خطأ: ' . $e->getMessage());
            Log::error('QUL Import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }

    private function seedQuranFromAPI(): void
    {
        $this->info('جاري جلب نص القرآن من API...');

        $response = Http::timeout(60)->get('https://api.alquran.cloud/v1/quran');

        if ($response->failed()) {
            $this->warn('فشل في جلب البيانات من API');
            return;
        }

        $data = $response->json();
        $surahs = $data['data']['surahs'] ?? [];

        DB::transaction(function () use ($surahs) {
            foreach ($surahs as $surahData) {
                $surah = Surah::updateOrCreate(
                    ['surah_number' => $surahData['number']],
                    [
                        'name_ar' => $surahData['name'],
                        'name_en' => $surahData['englishName'] ?? null,
                        'total_ayahs' => count($surahData['ayahs']),
                    ]
                );

                foreach ($surahData['ayahs'] as $ayahData) {
                    Ayah::updateOrCreate(
                        [
                            'surah_id' => $surah->id,
                            'ayah_number' => $ayahData['numberInSurah'],
                        ],
                        [
                            'text' => $ayahData['text'],
                            'page_number' => $ayahData['page'] ?? $this->calculatePageNumber($ayahData['number']),
                            'juz_number' => $ayahData['juz'] ?? $this->calculateJuzNumber($ayahData['number']),
                        ]
                    );
                }
            }
        });

        $this->info('تم استيراد ' . Surah::count() . ' سورة و ' . Ayah::count() . ' آية');
    }

    private function importWords(string $path): void
    {
        $this->info('جاري استيراد الكلمات...');

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (!$data || !is_array($data)) {
            $this->warn('ملف الكلمات فارغ أو تالف');
            return;
        }

        $inserted = 0;
        $batch = [];
        $batchSize = 500;

        // QUL JSON format varies; handle common structures
        $words = $data['words'] ?? $data['data'] ?? $data;

        foreach ($words as $word) {
            $batch[] = [
                'surah_number' => $word['surah_number'] ?? $word['surah'] ?? $word['chapter'] ?? 0,
                'ayah_number' => $word['ayah_number'] ?? $word['ayah'] ?? $word['verse'] ?? 0,
                'word_position' => $word['word_position'] ?? $word['position'] ?? 0,
                'text_uthmani' => $word['text_uthmani'] ?? $word['text'] ?? $word['word'] ?? '',
                'text_imlaei' => $word['text_imlaei'] ?? $word['imlaei'] ?? null,
                'text_indopak' => $word['text_indopak'] ?? $word['indopak'] ?? null,
                'transliteration' => $word['transliteration'] ?? null,
                'translation_en' => $word['translation_en'] ?? $word['translation'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                QuranWord::insertOrIgnore($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            QuranWord::insertOrIgnore($batch);
            $inserted += count($batch);
        }

        $this->info("تم استيراد {$inserted} كلمة");
    }

    private function setupReciter(): Reciter
    {
        $slug = $this->option('reciter');
        $name = $this->option('reciter-name');
        $baseUrl = $this->option('base-url') ?? 'https://cdn.islamic.network/quran/audio/128/ar.alafasy';

        return Reciter::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'style' => 'murattal',
                'is_segmented' => false,
                'audio_base_url' => $baseUrl,
                'is_active' => true,
            ]
        );
    }

    private function importSegments(string $path, int $reciterId): void
    {
        $this->info('جاري استيراد المقاطع الصوتية...');

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (!$data || !is_array($data)) {
            $this->warn('ملف المقاطع فارغ أو تالف');
            return;
        }

        // Mark reciter as segmented
        Reciter::where('id', $reciterId)->update(['is_segmented' => true]);

        $inserted = 0;
        $batch = [];
        $batchSize = 500;

        $segments = $data['segments'] ?? $data['audio_files'] ?? $data;

        foreach ($segments as $segment) {
            $batch[] = [
                'reciter_id' => $reciterId,
                'surah_number' => $segment['surah_number'] ?? $segment['surah'] ?? 0,
                'ayah_number' => $segment['ayah_number'] ?? $segment['ayah'] ?? 0,
                'word_position' => $segment['word_position'] ?? $segment['word'] ?? null,
                'audio_url' => $segment['audio_url'] ?? $segment['url'] ?? null,
                'start_time' => $segment['start_time'] ?? $segment['timestamp_from'] ?? null,
                'end_time' => $segment['end_time'] ?? $segment['timestamp_to'] ?? null,
                'duration' => $segment['duration'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                AudioSegment::insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            AudioSegment::insert($batch);
            $inserted += count($batch);
        }

        $this->info("تم استيراد {$inserted} مقطع صوتي");
    }

    private function createFallbackSegments(int $reciterId): void
    {
        $this->info('جاري إنشاء مقاطع من CDN fallback...');

        $reciter = Reciter::find($reciterId);
        if (!$reciter) return;

        $baseUrl = $reciter->audio_base_url;

        $ayahs = Ayah::with('surah')->get();
        $inserted = 0;
        $batch = [];
        $batchSize = 500;

        foreach ($ayahs as $ayah) {
            $globalNumber = $this->getGlobalAyahNumber($ayah->surah->surah_number, $ayah->ayah_number);
            if (!$globalNumber) continue;

            $batch[] = [
                'reciter_id' => $reciterId,
                'surah_number' => $ayah->surah->surah_number,
                'ayah_number' => $ayah->ayah_number,
                'word_position' => null, // full ayah
                'audio_url' => "{$baseUrl}/{$globalNumber}.mp3",
                'start_time' => null,
                'end_time' => null,
                'duration' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                AudioSegment::insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            AudioSegment::insert($batch);
            $inserted += count($batch);
        }

        $this->info("تم إنشاء {$inserted} مقطع صوتي من CDN");
    }

    private function getGlobalAyahNumber(int $surah, int $ayah): ?int
    {
        $ayahRecord = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', $surah))
            ->where('ayah_number', $ayah)
            ->first();

        // Approximate global number if not stored
        if (!$ayahRecord) return null;

        // Calculate approximate global number
        $count = Ayah::whereHas('surah', fn($q) => $q->where('surah_number', '<', $surah))->count();
        return $count + $ayah;
    }

    private function calculatePageNumber(int $ayahNumber): int
    {
        return (int) ceil($ayahNumber / 10.4);
    }

    private function calculateJuzNumber(int $ayahNumber): int
    {
        $boundaries = [1, 22, 42, 62, 82, 102, 122, 142, 162, 182, 202, 222, 242, 262, 282, 302, 322, 342, 362, 382, 402, 422, 442, 462, 482, 502, 522, 542, 562, 582, 602, 6236];

        for ($i = 1; $i < count($boundaries); $i++) {
            if ($ayahNumber < $boundaries[$i]) {
                return $i;
            }
        }
        return 30;
    }
}
