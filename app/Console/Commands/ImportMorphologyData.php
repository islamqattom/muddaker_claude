<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\WordConcordance;

class ImportMorphologyData extends Command
{
    protected $signature = 'morphology:import
                            {--source=mustafa0x : Source: mustafa0x (default), corpus, local}
                            {--path= : Local path for import/export}
                            {--chunk=1000 : Number of lines to process per batch}';

    protected $description = 'Import Quran morphology data from open sources (mustafa0x/quran-morphology)';

    private int $totalImported = 0;
    private int $totalSkipped = 0;
    private array $errors = [];
    private array $terms = [];
    private int $currentSurah = 0;
    private int $currentAyah = 0;

    public function handle(): int
    {
        $this->info('بدء استيراد بيانات الإعراب والصرف...');

        try {
            $this->loadTerms();

            $source = $this->option('source');

            if ($source === 'mustafa0x') {
                $this->importFromMustafa0x();
            } elseif ($source === 'corpus') {
                $this->importFromCorpus();
            } elseif ($source === 'local') {
                $path = $this->option('path');
                if (!$path) {
                    $this->error('يرجى تحديد المسار باستخدام --path');
                    return self::FAILURE;
                }
                $this->importFromLocal($path);
            } else {
                $this->error('المصدر غير معروف: ' . $source);
                return self::FAILURE;
            }

            $this->showSummary();
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('حدث خطأ: ' . $e->getMessage());
            Log::error('Morphology import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }

    private function loadTerms(): void
    {
        $this->info('جاري تحميل قائمة المصطلحات...');

        $json = file_get_contents('https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/morphology-terms-ar.json');
        $this->terms = json_decode($json, true);

        $this->info('تم تحميل ' . count($this->terms) . ' مجموعة مصطلحات');
    }

    private function importFromMustafa0x(): void
    {
        $this->info('جاري تحميل البيانات من mustafa0x/quran-morphology...');

        $url = 'https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/quran-morphology.txt';

        $this->info('جاري تحميل ملف البيانات (قد يستغرق بضع دقائق)...');

        $context = stream_context_create([
            'http' => [
                'timeout' => 300,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $lines = @file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES, $context);

        if (!$lines) {
            $this->warn('فشل تحميل الملف مباشرة، جاري استخدام طريقة بديلة...');
            $this->importViaStreaming($url);
            return;
        }

        $this->info('تم تحميل ' . count($lines) . ' سطر');

        $this->processLines($lines);
    }

    private function importViaStreaming(string $url): void
    {
        $this->info('جاري الاستيراد عبر التدفق (streaming)...');

        $handle = fopen($url, 'r');
        if (!$handle) {
            throw new \Exception('فشل في فتح الاتصال');
        }

        $batch = [];
        $batchSize = (int) $this->option('chunk');
        $lineNumber = 0;

        while (!feof($handle)) {
            $line = fgets($handle);
            $lineNumber++;

            if ($lineNumber <= 57) continue;

            $line = trim($line);
            if (empty($line)) continue;

            $record = $this->parseMorphologyLine($line);
            if ($record) {
                $batch[] = $record;
            }

            if (count($batch) >= $batchSize) {
                $this->insertBatch($batch);
                $this->info("  تم معالجة ~{$this->totalImported} كلمة (سطر {$lineNumber})");
                $batch = [];
            }

            if ($lineNumber % 10000 === 0) {
                $this->info("  جاري المعالجة... سطر {$lineNumber}");
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }
    }

    private function importFromCorpus(): void
    {
        $this->info('جاري الاستيراد من Quranic Arabic Corpus...');

        for ($surah = 1; $surah <= 114; $surah++) {
            $totalAyahs = $this->getSurahAyahCount($surah);

            for ($ayah = 1; $ayah <= $totalAyahs; $ayah++) {
                $this->fetchAndStoreMorphology($surah, $ayah);
            }

            $this->info("  اكتملت سورة {$surah}");
        }
    }

    private function importFromLocal(string $path): void
    {
        $this->info('جاري الاستيراد من المسار المحلي: ' . $path);

        if (!file_exists($path)) {
            throw new \Exception('المسار غير موجود: ' . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lines) {
            throw new \Exception('فشل في قراءة الملف');
        }

        $this->info('تم تحميل ' . count($lines) . ' سطر');

        $this->processLines($lines);
    }

    private function processLines(array $lines): void
    {
        $batch = [];
        $batchSize = (int) $this->option('chunk');

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $record = $this->parseMorphologyLine($line);
            if ($record) {
                $batch[] = $record;
            }

            if (count($batch) >= $batchSize) {
                $this->insertBatch($batch);
                $batch = [];
                $this->info("  تم معالجة {$this->totalImported} كلمة...");
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }
    }

    private function parseMorphologyLine(string $line): ?array
    {
        $parts = explode("\t", $line);

        if (count($parts) < 4) {
            return null;
        }

        $location = trim($parts[0], '()');
        $locationParts = explode(':', $location);

        if (count($locationParts) < 3) {
            return null;
        }

        $surah = (int) $locationParts[0];
        $ayah = (int) $locationParts[1];
        $wordPosition = (int) $locationParts[2];

        $wordText = $parts[1];
        $posTag = $parts[2];
        $analysis = $parts[3] ?? '';

        if ($this->currentSurah !== $surah) {
            $this->currentSurah = $surah;
            $this->info("  جاري معالجة السورة {$surah}...");
        }
        $this->currentAyah = $ayah;

        $parsed = $this->parseAnalysis($analysis);

        return [
            'surah_number' => $surah,
            'ayah_number' => $ayah,
            'word_position' => $wordPosition,
            'word_text' => $wordText,
            'part_of_speech' => $this->mapPosToArabic($posTag, $parsed['type']),
            'part_of_speech_raw' => $posTag,
            'root' => $parsed['root'],
            'lemma' => $parsed['lemma'],
            'morphology' => $analysis,
            'gender' => $parsed['gender'],
            'number' => $parsed['number'],
            'person' => $parsed['person'],
            'tense' => $parsed['tense'],
            'voice' => $parsed['voice'],
            'case' => $parsed['case'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function parseAnalysis(string $analysis): array
    {
        $result = [
            'root' => null,
            'lemma' => null,
            'type' => null,
            'gender' => null,
            'number' => null,
            'person' => null,
            'tense' => null,
            'voice' => null,
            'case' => null,
        ];

        $tags = explode('|', $analysis);

        foreach ($tags as $tag) {
            $tag = trim($tag);

            if (strpos($tag, 'STEM|POS:') === 0) {
                $result['type'] = substr($tag, 8);
            } elseif (strpos($tag, 'ROOT:') === 0) {
                $result['root'] = substr($tag, 5);
            } elseif (strpos($tag, 'LEM:') === 0) {
                $result['lemma'] = substr($tag, 4);
            } elseif (strpos($tag, 'VF:') === 0) {
                $result['tense'] = 'VF:' . substr($tag, 3);
            } elseif (preg_match('/^[MF]$/', $tag)) {
                $result['gender'] = $tag === 'M' ? 'masculine' : 'feminine';
            } elseif (preg_match('/^[SDP]$/', $tag)) {
                $result['number'] = $tag === 'S' ? 'singular' : ($tag === 'D' ? 'dual' : 'plural');
            } elseif (preg_match('/^[123]$/', $tag)) {
                $result['person'] = $tag === '1' ? 'first' : ($tag === '2' ? 'second' : 'third');
            } elseif (in_array($tag, ['PERF', 'IMPF', 'IMPV'])) {
                $result['tense'] = $tag;
            } elseif (in_array($tag, ['NOM', 'ACC', 'GEN'])) {
                $result['case'] = $tag === 'NOM' ? 'nominative' : ($tag === 'ACC' ? 'accusative' : 'genitive');
            } elseif (in_array($tag, ['PASS'])) {
                $result['voice'] = 'passive';
            }
        }

        return $result;
    }

    private function mapPosToArabic(string $posTag, ?string $type): string
    {
        if (!$type) {
            return $posTag;
        }

        $posMap = [
            'N' => 'اسم',
            'V' => 'فعل',
            'P' => 'حرف',
            'PRON' => 'ضمير',
            'PN' => 'علم',
            'DEM' => 'اسم إشارة',
            'REL' => 'اسم موصول',
            'T' => 'ظرف زمان',
            'LOC' => 'ظرف مكان',
            'COND' => 'شرطية',
            'INTG' => 'استفهام',
            'NV' => 'اسم فعل',
        ];

        return $posMap[$type] ?? $type;
    }

    private function insertBatch(array $batch): void
    {
        try {
            foreach ($batch as $record) {
                unset($record['part_of_speech_raw']);

                DB::table('word_concordance')
                    ->updateOrInsert(
                        [
                            'surah_number' => $record['surah_number'],
                            'ayah_number' => $record['ayah_number'],
                            'word_position' => $record['word_position'],
                        ],
                        $record
                    );
                $this->totalImported++;
            }
        } catch (\Exception $e) {
            $this->errors[] = 'Batch insert failed: ' . $e->getMessage();
            $this->totalSkipped += count($batch);
        }
    }

    private function fetchAndStoreMorphology(int $surah, int $ayah): void
    {
        $url = "https://furqan.co/ayah-morph/{$surah}/{$ayah}";

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $this->parseAndStoreFromHtml($html, $surah, $ayah);
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        usleep(100000);
    }

    private function parseAndStoreFromHtml(string $html, int $surah, int $ayah): void
    {
        $pattern = '/﴿([^﴾]+)﴾\s*([^،\.]+)/u';
        $position = 1;

        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[1] as $index => $word) {
                $analysis = trim($matches[2][$index]);

                DB::table('word_concordance')
                    ->updateOrInsert(
                        [
                            'surah_number' => $surah,
                            'ayah_number' => $ayah,
                            'word_position' => $position,
                        ],
                        [
                            'word_text' => $word,
                            'morphology' => $analysis,
                            'part_of_speech' => $analysis,
                            'updated_at' => now(),
                        ]
                    );

                $position++;
                $this->totalImported++;
            }
        }
    }

    private function getSurahAyahCount(int $surah): int
    {
        static $surahInfo = null;

        if (!$surahInfo) {
            $surahInfo = [
                1 => 7, 2 => 286, 3 => 200, 4 => 176, 5 => 120, 6 => 165, 7 => 206,
                8 => 75, 9 => 129, 10 => 109, 11 => 123, 12 => 111, 13 => 43, 14 => 52,
                15 => 99, 16 => 128, 17 => 111, 18 => 110, 19 => 98, 20 => 135, 21 => 112,
                22 => 78, 23 => 118, 24 => 64, 25 => 77, 26 => 227, 27 => 93, 28 => 88,
                29 => 69, 30 => 60, 31 => 34, 32 => 30, 33 => 73, 34 => 54, 35 => 45,
                36 => 83, 37 => 182, 38 => 88, 39 => 75, 40 => 85, 41 => 54, 42 => 53,
                43 => 89, 44 => 59, 45 => 37, 46 => 35, 47 => 38, 48 => 29, 49 => 18,
                50 => 45, 51 => 60, 52 => 49, 53 => 62, 54 => 55, 55 => 78, 56 => 96,
                57 => 29, 58 => 22, 59 => 24, 60 => 13, 61 => 14, 62 => 11, 63 => 11,
                64 => 18, 65 => 12, 66 => 12, 67 => 30, 68 => 52, 69 => 52, 70 => 44,
                71 => 28, 72 => 28, 73 => 20, 74 => 56, 75 => 40, 76 => 31, 77 => 50,
                78 => 40, 79 => 46, 80 => 42, 81 => 29, 82 => 19, 83 => 36, 84 => 25,
                85 => 22, 86 => 17, 87 => 19, 88 => 26, 89 => 30, 90 => 20, 91 => 15,
                92 => 21, 93 => 11, 94 => 8, 95 => 8, 96 => 19, 97 => 5, 98 => 8,
                99 => 8, 100 => 11, 101 => 11, 102 => 8, 103 => 3, 104 => 9, 105 => 5,
                106 => 4, 107 => 7, 108 => 3, 109 => 6, 110 => 3, 111 => 5, 112 => 4,
                113 => 5, 114 => 6
            ];
        }

        return $surahInfo[$surah] ?? 0;
    }

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('================== ملخص الاستيراد ==================');
        $this->info('إجمالي السجلات المستوردة: ' . $this->totalImported);
        $this->info('إجمالي السجلات المتخطاة: ' . $this->totalSkipped);

        if (!empty($this->errors)) {
            $this->warn('الأخطاء:');
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->warn('  - ' . $error);
            }
            if (count($this->errors) > 10) {
                $this->warn('  ... و ' . (count($this->errors) - 10) . ' أخطاء أخرى');
            }
        }

        $this->newLine();
        $this->info('الجدول: word_concordance');
        $this->info('عدد السجلات الحالي: ' . DB::table('word_concordance')->count());
    }
}