<?php

namespace App\Console\Commands;

use App\Models\Ayah;
use App\Models\MafsilPosition;
use App\Models\Surah;
use Illuminate\Console\Command;

class VerifyMafsilPositions extends Command
{
    protected $signature = 'mafsil:verify-positions {file=المواضع.docx : مسار ملف Word} {--juz= : التحقق من جزء محدد فقط}';
    protected $description = 'مقارنة مواضع المفصل المخزنة في قاعدة البيانات بملف Word';

    private const ARABIC_DIGITS = [
        '٠' => '0',
        '١' => '1',
        '٢' => '2',
        '٣' => '3',
        '٤' => '4',
        '٥' => '5',
        '٦' => '6',
        '٧' => '7',
        '٨' => '8',
        '٩' => '9',
    ];

    private const JUZ_ORDINALS = [
        'الثلاثون' => 30,
        'التاسع والعشرون' => 29,
        'الثامن والعشرون' => 28,
        'السابع والعشرون' => 27,
        'السادس والعشرون' => 26,
        'الخامس والعشرون' => 25,
        'الرابع والعشرون' => 24,
        'الثالث والعشرون' => 23,
        'الثاني والعشرون' => 22,
        'الحادي والعشرون' => 21,
        'العشرون' => 20,
        'التاسع عشر' => 19,
        'الثامن عشر' => 18,
        'السابع عشر' => 17,
        'السادس عشر' => 16,
        'الخامس عشر' => 15,
        'الرابع عشر' => 14,
        'الثالث عشر' => 13,
        'الثاني عشر' => 12,
        'الحادي عشر' => 11,
        'العاشر' => 10,
        'التاسع' => 9,
        'الثامن' => 8,
        'السابع' => 7,
        'السادس' => 6,
        'الخامس' => 5,
        'الرابع' => 4,
        'الثالث' => 3,
        'الثاني' => 2,
        'الأول' => 1,
    ];

    private array $surahMap = [];
    private array $surahNumberToName = [];
    private ?int $currentJuz = null;
    private array $entriesByJuz = [];
    private array $topEntriesByJuz = [];
    private array $bottomEntriesByJuz = [];
    private array $lastSurahIndexByJuz = [];
    private array $wholeSurahCounterByJuz = [];

    public function handle(): int
    {
        $file = $this->argument('file');
        $path = base_path($file);

        if (! file_exists($path)) {
            $this->error("الملف غير موجود: {$path}");
            return self::FAILURE;
        }

        $this->loadSurahs();
        $this->parseDocx($path);
        $this->verify();

        return self::SUCCESS;
    }

    private function loadSurahs(): void
    {
        foreach (Surah::all() as $surah) {
            $normalized = $this->normalizeSurahName($surah->name_ar);
            $this->surahMap[$normalized] = $surah->surah_number;
            $this->surahNumberToName[$surah->surah_number] = $normalized;
        }
    }

    private function normalizeSurahName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $name);
        $name = str_replace(['ٱ', 'إ', 'أ', 'آ'], 'ا', $name);
        $name = preg_replace('/^(سورة|سوره)\s+/u', '', $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return trim($name);
    }

    private function findSurahNumber(string $name): ?int
    {
        $normalized = $this->normalizeSurahName($name);

        if (isset($this->surahMap[$normalized])) {
            return $this->surahMap[$normalized];
        }

        $bestNumber = null;
        $bestDistance = PHP_INT_MAX;

        foreach ($this->surahMap as $candidate => $number) {
            $distance = levenshtein($normalized, $candidate);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestNumber = $number;
            }
        }

        $threshold = match (true) {
            strlen($normalized) <= 4 => 1,
            strlen($normalized) <= 8 => 2,
            default => 3,
        };

        if ($bestDistance <= $threshold) {
            return $bestNumber;
        }

        return null;
    }

    private function parseDocx(string $path): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            $this->error('تعذر فتح ملف Word');
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $dom = new \DOMDocument;
        $dom->loadXML($xml);

        foreach ($dom->getElementsByTagName('p') as $paragraph) {
            $line = trim(str_replace(["\r", "\n"], ' ', $paragraph->textContent));
            if ($line === '') {
                continue;
            }

            $this->processLine($line);
        }
    }

    private function processLine(string $line): void
    {
        if (preg_match('/^(.*?)(الجزء\s+.+)$/u', $line, $matches)) {
            $newJuz = $this->parseJuzHeader($matches[2]);
            if ($newJuz !== null) {
                $positionLine = trim($matches[1]);
                if ($positionLine !== '') {
                    $this->processPositionLine($positionLine);
                }
                $this->currentJuz = $newJuz;
                return;
            }
        }

        $this->processPositionLine($line);
    }

    private function processPositionLine(string $line): void
    {
        if ($this->currentJuz === null) {
            return;
        }

        $parsedTop = $this->parseTopFormat($line);
        if ($parsedTop) {
            $this->addEntry($parsedTop, 'top');
            return;
        }

        $parsedBottom = $this->parseBottomFormat($line);
        if ($parsedBottom) {
            $this->addEntry($parsedBottom, 'bottom');
            return;
        }
    }

    private function parseJuzHeader(string $line): ?int
    {
        $line = preg_replace('/[:：].*$/u', '', $line);
        if (! str_starts_with($line, 'الجزء')) {
            return null;
        }

        $rest = trim(substr($line, strlen('الجزء')));

        if (preg_match('/^(\d+)$/u', $rest, $matches)) {
            return (int) $matches[1];
        }

        foreach (self::JUZ_ORDINALS as $ordinal => $number) {
            if ($rest === $ordinal || str_starts_with($rest, $ordinal . ' ') || str_starts_with($rest, $ordinal . '-')) {
                return $number;
            }
        }

        return null;
    }

    private function parseTopFormat(string $line): ?array
    {
        if (! str_starts_with(trim($line), '*')) {
            return null;
        }

        $pattern = '/^\*\s*الموضع\s+([\d\s]+)\s*من\s*(?:آية\s+)?(?:رقم\s+)?([\d\s]+)\s*إلى\s*(?:آية\s+)?([\d\s]*)(?:\(([^)]*)\))?/u';

        if (! preg_match($pattern, $line, $matches)) {
            return null;
        }

        $position = (int) $this->toWestern($matches[1]);
        $start = (int) $this->toWestern($matches[2]);
        $endRaw = trim($this->toWestern($matches[3]));
        $end = $endRaw === '' ? null : (int) $endRaw;
        $difficultyText = trim($matches[4] ?? '');

        return [
            'format' => 'top',
            'position' => $position,
            'start' => $start,
            'end' => $end,
            'difficulty' => $this->parseDifficulty($difficultyText),
            'raw' => $line,
        ];
    }

    private function parseBottomFormat(string $line): ?array
    {
        if (preg_match('/^\s*(\d+)\s*[-–]\s*(.+?)(?:\(([^)]*)\))?\s*$/u', $line, $matches)) {
            $position = (int) $matches[1];
            $core = trim($matches[2]);
            $difficultyText = trim($matches[3] ?? '');
        } else {
            return null;
        }

        $core = preg_replace('/\s+\(\s*[^)]*\s*\)\s*$/u', '', $core);
        $segments = preg_split('/\s*-\s*/u', $core);
        $segments = array_map('trim', array_filter($segments, fn ($s) => trim($s) !== ''));

        $parsedSegments = [];
        foreach ($segments as $segment) {
            if (preg_match('/^(\d+)$/u', $segment, $m)) {
                $parsedSegments[] = ['type' => 'number', 'number' => (int) $m[1]];
                continue;
            }

            if (preg_match('/^(.+?)\s*(\d+)$/u', $segment, $m)) {
                $name = trim($m[1]);
                $number = (int) $m[2];
            } else {
                $name = $segment;
                $number = null;
            }

            $parsedSegments[] = [
                'type' => 'name',
                'name' => $name,
                'number' => $number,
                'surah' => $this->findSurahNumber($name),
            ];
        }

        $startIndex = null;
        foreach ($parsedSegments as $i => $segment) {
            if ($segment['type'] === 'name') {
                $startIndex = $i;
                break;
            }
        }

        if ($startIndex === null) {
            return null;
        }

        $startSegment = $parsedSegments[$startIndex];
        $startSurah = $startSegment['surah'];
        $startAyah = $startSegment['number'] ?? 1;

        $endSurah = $startSurah;
        $endAyah = null;
        $nextIndex = $startIndex + 1;

        if (isset($parsedSegments[$nextIndex])) {
            $next = $parsedSegments[$nextIndex];
            if ($next['type'] === 'number') {
                $endAyah = $next['number'];
                $endSurah = $startSurah;
            } elseif ($next['type'] === 'name') {
                $endSurah = $next['surah'];
                $endAyah = $next['number'];

            if (isset($parsedSegments[$nextIndex + 1]) && $parsedSegments[$nextIndex + 1]['type'] === 'number') {
                $endAyah = $parsedSegments[$nextIndex + 1]['number'];
            }

            // Correct start/end when a line lists two ayah numbers before a surah name
            // (e.g. "7- الشعراء 17 34")
            if ($endSurah === $startSurah
                && $endAyah !== null
                && count($parsedSegments) >= 4
                && $parsedSegments[$startIndex + 1]['type'] === 'number'
                && $parsedSegments[$startIndex + 2]['type'] === 'name'
            ) {
                $startAyah = $parsedSegments[$startIndex + 1]['number'];
                $endSurah = $parsedSegments[$startIndex + 2]['surah'];
                $endAyah = $parsedSegments[$startIndex + 2]['number']
                    ?? $parsedSegments[$startIndex + 3]['number']
                    ?? null;
            }
        }
        }

        return [
            'format' => 'bottom',
            'position' => $position,
            'difficulty' => $this->parseDifficulty($difficultyText),
            'start_surah' => $startSurah,
            'start_ayah' => $startAyah,
            'end_surah' => $endSurah,
            'end_ayah' => $endAyah,
            'raw' => $line,
        ];
    }

    private function parseDifficulty(string $text): int
    {
        $text = trim($text);
        if ($text === '') {
            return 2;
        }
        if (str_contains($text, 'صعب')) {
            return 3;
        }
        if (str_contains($text, 'متوسط')) {
            return 2;
        }
        if (str_contains($text, 'سهل')) {
            return 1;
        }

        return 2;
    }

    private function toWestern(string $text): string
    {
        return strtr($text, self::ARABIC_DIGITS);
    }

    private function addEntry(array $parsed, string $source): void
    {
        $juz = $this->currentJuz;
        $bucket = match ($source) {
            'top' => 'topEntriesByJuz',
            'bottom' => 'bottomEntriesByJuz',
            default => 'entriesByJuz',
        };

        if (! isset($this->{$bucket}[$juz])) {
            $this->{$bucket}[$juz] = [];
        }

        $listOrder = count($this->{$bucket}[$juz]) + 1;
        $resolved = $this->resolveEntry($parsed, $juz);

        $this->{$bucket}[$juz][] = [
            'juz' => $juz,
            'list_order' => $listOrder,
            'position_number' => $parsed['position'] ?? null,
            'difficulty' => $parsed['difficulty'],
            'start_ayah_id' => $resolved['start_ayah_id'] ?? null,
            'end_ayah_id' => $resolved['end_ayah_id'] ?? null,
            'start_surah_number' => $resolved['start_surah_number'] ?? null,
            'start_ayah_number' => $resolved['start_ayah_number'] ?? null,
            'end_surah_number' => $resolved['end_surah_number'] ?? null,
            'end_ayah_number' => $resolved['end_ayah_number'] ?? null,
            'error' => $resolved['error'] ?? null,
            'raw' => $parsed['raw'],
        ];
    }

    private function resolveEntry(array $parsed, int $juz): array
    {
        return $parsed['format'] === 'top'
            ? $this->resolveTopEntry($parsed, $juz)
            : $this->resolveBottomEntry($parsed, $juz);
    }

    private function resolveTopEntry(array $parsed, int $juz): array
    {
        $resolved = $this->resolveAyahsInJuz($juz, $parsed['start'], $parsed['end']);

        if ($resolved === null) {
            return ['error' => 'تعذر تحديد الآيات'];
        }

        return [
            'start_ayah_id' => $resolved['start_ayah_id'],
            'end_ayah_id' => $resolved['end_ayah_id'],
            'start_surah_number' => $resolved['start_surah_number'],
            'start_ayah_number' => $resolved['start_ayah_number'],
            'end_surah_number' => $resolved['end_surah_number'],
            'end_ayah_number' => $resolved['end_ayah_number'],
        ];
    }

    private function resolveBottomEntry(array $parsed, int $juz): array
    {
        if ($parsed['start_surah'] === null) {
            return ['error' => 'السورة غير معروفة: ' . $parsed['start_ayah']];
        }

        $startAyah = $this->findAyahInJuz($juz, $parsed['start_surah'], $parsed['start_ayah']);
        if ($startAyah === null) {
            return ['error' => "آية البداية غير موجودة في الجزء: {$parsed['start_surah']}:{$parsed['start_ayah']}"];
        }

        $endSurah = $parsed['end_surah'] ?? $parsed['start_surah'];
        $endAyahNumber = $parsed['end_ayah'];

        if ($endAyahNumber === null) {
            $endAyah = Ayah::with('surah')
                ->whereHas('surah', fn ($q) => $q->where('surah_number', $endSurah))
                ->where('juz_number', $juz)
                ->orderByDesc('ayah_number')
                ->first();
        } else {
            $endAyah = $this->findAyahInJuz($juz, $endSurah, $endAyahNumber);
        }

        if ($endAyah === null) {
            return ['error' => "آية النهاية غير موجودة في الجزء: {$endSurah}:{$endAyahNumber}"];
        }

        return [
            'start_ayah_id' => $startAyah->id,
            'end_ayah_id' => $endAyah->id,
            'start_surah_number' => $startAyah->surah->surah_number,
            'start_ayah_number' => $startAyah->ayah_number,
            'end_surah_number' => $endAyah->surah->surah_number,
            'end_ayah_number' => $endAyah->ayah_number,
        ];
    }

    private function findAyahInJuz(int $juz, int $surahNumber, int $ayahNumber): ?Ayah
    {
        return Ayah::with('surah')
            ->whereHas('surah', fn ($q) => $q->where('surah_number', $surahNumber))
            ->where('juz_number', $juz)
            ->where('ayah_number', $ayahNumber)
            ->first();
    }

    private function resolveAyahsInJuz(int $juz, int $startAyahNumber, ?int $endAyahNumber): ?array
    {
        $ayahsBySurah = Ayah::query()
            ->with('surah')
            ->where('juz_number', $juz)
            ->orderBy('id')
            ->get()
            ->groupBy('surah_id')
            ->sortKeys()
            ->values();

        if ($ayahsBySurah->isEmpty()) {
            return null;
        }

        $surahs = [];
        foreach ($ayahsBySurah as $group) {
            $ayahNumbers = $group->pluck('ayah_number')->all();
            $surahs[] = [
                'surah_id' => $group->first()->surah_id,
                'surah_number' => $group->first()->surah->surah_number,
                'ayah_by_number' => $group->keyBy('ayah_number'),
                'ayah_numbers' => $ayahNumbers,
                'min' => min($ayahNumbers),
                'max' => max($ayahNumbers),
            ];
        }

        $startIndex = $this->lastSurahIndexByJuz[$juz] ?? 0;
        $startSurahIndex = $this->findSurahForAyah($surahs, $startAyahNumber, $startIndex);
        if ($startSurahIndex === null) {
            $startSurahIndex = $this->findSurahForAyah($surahs, $startAyahNumber, 0);
        }
        if ($startSurahIndex === null) {
            return null;
        }

        $startSurah = $surahs[$startSurahIndex];
        $startAyah = $startSurah['ayah_by_number'][$startAyahNumber] ?? null;
        if ($startAyah === null) {
            return null;
        }

        if ($endAyahNumber === null) {
            $lastAyah = $this->getLastAyahInJuz($juz);
            if ($lastAyah === null) {
                return null;
            }
            $endAyahNumber = $lastAyah->ayah_number;
        } else {
            $lastAyah = $this->getLastAyahInJuz($juz);
            if ($lastAyah !== null && $endAyahNumber > $lastAyah->ayah_number) {
                $endAyahNumber = $lastAyah->ayah_number;
            }
        }

        $endSurahIndex = $this->findSurahForAyah($surahs, $endAyahNumber, $startSurahIndex);
        if ($endSurahIndex === null || ($startAyahNumber > $endAyahNumber && $endSurahIndex === $startSurahIndex)) {
            $endSurahIndex = $this->findSurahForAyah($surahs, $endAyahNumber, $startSurahIndex + 1);
            if ($endSurahIndex === null && count($surahs) > $startSurahIndex + 2) {
                $endSurahIndex = $this->findSurahForAyah($surahs, $endAyahNumber, $startSurahIndex + 2);
            }
            if ($endSurahIndex === null && count($surahs) > $startSurahIndex + 3) {
                $endSurahIndex = $this->findSurahForAyah($surahs, $endAyahNumber, $startSurahIndex + 3);
            }
        }

        if ($endSurahIndex === null) {
            return null;
        }

        $endSurah = $surahs[$endSurahIndex];
        $endAyah = $endSurah['ayah_by_number'][$endAyahNumber] ?? null;
        if ($endAyah === null) {
            return null;
        }

        $this->lastSurahIndexByJuz[$juz] = $endSurahIndex;

        return [
            'start_ayah_id' => $startAyah->id,
            'end_ayah_id' => $endAyah->id,
            'start_surah_number' => $startAyah->surah->surah_number,
            'start_ayah_number' => $startAyah->ayah_number,
            'end_surah_number' => $endAyah->surah->surah_number,
            'end_ayah_number' => $endAyah->ayah_number,
        ];
    }

    private function getLastAyahInJuz(int $juz): ?Ayah
    {
        return Ayah::query()
            ->where('juz_number', $juz)
            ->orderByDesc('id')
            ->first();
    }

    private function findSurahForAyah(array $surahs, int $ayahNumber, int $fromIndex): ?int
    {
        for ($i = $fromIndex; $i < count($surahs); $i++) {
            if (in_array($ayahNumber, $surahs[$i]['ayah_numbers'], true)) {
                return $i;
            }
        }

        return null;
    }

    private function verify(): void
    {
        $onlyJuz = $this->option('juz') ? (int) $this->option('juz') : null;

        $juzNumbers = $onlyJuz !== null
            ? [$onlyJuz]
            : array_unique(array_merge(
                array_keys($this->topEntriesByJuz),
                array_keys($this->bottomEntriesByJuz),
                MafsilPosition::pluck('juz_number')->all()
            ));

        sort($juzNumbers);

        $topMismatches = [];
        $topMatched = 0;
        $topFileCount = 0;
        $bottomMismatches = [];
        $bottomMatched = 0;
        $bottomFileCount = 0;
        $dbCount = 0;

        foreach ($juzNumbers as $juz) {
            $actual = MafsilPosition::with(['startAyah.surah', 'endAyah.surah'])
                ->where('juz_number', $juz)
                ->orderBy('list_order')
                ->get();
            $dbCount += $actual->count();

            $topExpected = $this->topEntriesByJuz[$juz] ?? [];
            $topFileCount += count($topExpected);
            $topResult = $this->compareEntries($topExpected, $actual, 'نسخة الموضع');
            $topMismatches = array_merge($topMismatches, $topResult['mismatches']);
            $topMatched += $topResult['matched'];

            $bottomExpected = $this->bottomEntriesByJuz[$juz] ?? [];
            $bottomFileCount += count($bottomExpected);
            $bottomResult = $this->compareEntries($bottomExpected, $actual, 'نسخة السور');
            $bottomMismatches = array_merge($bottomMismatches, $bottomResult['mismatches']);
            $bottomMatched += $bottomResult['matched'];
        }

        $this->section('مقارنة النسخة العلوية (الموضع X من آية Y إلى Z)');
        $this->printMismatches($topMismatches, $topMatched, $topFileCount, $dbCount);

        $this->newLine();
        $this->section('مقارنة النسخة السفلية (سورة + آيات)');
        $this->printMismatches($bottomMismatches, $bottomMatched, $bottomFileCount, $dbCount);
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->info(str_repeat('=', 60));
        $this->info($title);
        $this->info(str_repeat('=', 60));
    }

    private function compareEntries(array $expected, $actual, string $sourceLabel): array
    {
        $mismatches = [];
        $matched = 0;

        $max = max(count($expected), $actual->count());
        for ($i = 0; $i < $max; $i++) {
            $exp = $expected[$i] ?? null;
            $act = $actual[$i] ?? null;

            if ($exp && $act) {
                if ($exp['error']) {
                    $mismatches[] = [
                        'juz' => $juz ?? $act->juz_number,
                        'order' => $act->list_order,
                        'issue' => 'خطأ في الملف: ' . $exp['error'],
                        'expected' => $exp['raw'],
                        'actual' => $this->formatDbRow($act),
                    ];
                    continue;
                }

                $issues = [];
                if ($exp['position_number'] != $act->position_number) {
                    $issues[] = 'رقم الموضع';
                }
                if ($exp['start_ayah_id'] != $act->start_ayah_id) {
                    $issues[] = 'آية البداية';
                }
                if ($exp['end_ayah_id'] != $act->end_ayah_id) {
                    $issues[] = 'آية النهاية';
                }
                if ($exp['difficulty'] != $act->difficulty) {
                    $issues[] = 'الصعوبة';
                }

                if (! empty($issues)) {
                    $mismatches[] = [
                        'juz' => $juz ?? $act->juz_number,
                        'order' => $act->list_order,
                        'issue' => implode('، ', $issues),
                        'expected' => $this->formatExpectedRow($exp),
                        'actual' => $this->formatDbRow($act),
                    ];
                } else {
                    $matched++;
                }
            } elseif ($exp && ! $act) {
                $mismatches[] = [
                    'juz' => $juz ?? $exp['juz'],
                    'order' => $exp['list_order'],
                    'issue' => 'غير موجود في قاعدة البيانات',
                    'expected' => $this->formatExpectedRow($exp),
                    'actual' => '-',
                ];
            } elseif (! $exp && $act) {
                $mismatches[] = [
                    'juz' => $juz ?? $act->juz_number,
                    'order' => $act->list_order,
                    'issue' => 'زيادة في قاعدة البيانات',
                    'expected' => '-',
                    'actual' => $this->formatDbRow($act),
                ];
            }
        }

        return ['mismatches' => $mismatches, 'matched' => $matched];
    }

    private function printMismatches(array $mismatches, int $matched, int $fileCount, int $dbCount): void
    {
        if (! empty($mismatches)) {
            $this->table(
                ['الجزء', 'الترتيب', 'الخلل', 'المتوقع من الملف', 'الموجود في DB'],
                array_map(fn ($m) => [$m['juz'], $m['order'], $m['issue'], $m['expected'], $m['actual']], $mismatches)
            );
        }

        $this->newLine();
        $this->info("عدد المواضع في الملف: {$fileCount}");
        $this->info("عدد المواضع في DB: {$dbCount}");
        $this->info("المتطابقة: {$matched}");
        $this->info('الاختلافات: ' . count($mismatches));

        if (empty($mismatches)) {
            $this->info('جميع المواضع متطابقة.');
        }
    }

    private function formatExpectedRow(array $exp): string
    {
        if ($exp['error']) {
            return $exp['raw'];
        }

        $start = $this->surahName($exp['start_surah_number']) . ' ' . $exp['start_ayah_number'];
        $end = $this->surahName($exp['end_surah_number']) . ' ' . $exp['end_ayah_number'];
        $range = $start === $end ? $start : "{$start} - {$end}";

        return "موضع {$exp['position_number']}: {$range} (" . $this->difficultyLabel($exp['difficulty']) . ')';
    }

    private function formatDbRow(MafsilPosition $position): string
    {
        $start = $position->startAyah;
        $end = $position->endAyah;

        if (! $start || ! $end) {
            return "موضع {$position->position_number}: غير مكتمل";
        }

        $startText = $this->surahName($start->surah->surah_number) . ' ' . $start->ayah_number;
        $endText = $this->surahName($end->surah->surah_number) . ' ' . $end->ayah_number;
        $range = $start->surah_id === $end->surah_id ? $startText : "{$startText} - {$endText}";

        return "موضع {$position->position_number}: {$range} (" . $this->difficultyLabel($position->difficulty) . ')';
    }

    private function surahName(?int $number): string
    {
        if ($number === null) {
            return '؟';
        }

        return $this->surahNumberToName[$number] ?? (string) $number;
    }

    private function difficultyLabel(int $difficulty): string
    {
        return match ($difficulty) {
            1 => 'سهل',
            3 => 'صعب',
            default => 'متوسط',
        };
    }
}
