<?php

namespace App\Console\Commands;

use App\Models\Ayah;
use App\Models\MafsilPosition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMafsilPositions extends Command
{
    protected $signature = 'mafsil:import-positions {--file=database/data/mafsil_positions.txt : Path to the positions text file} {--fresh : Delete all existing positions before importing (destructive; will null FK references)}';
    protected $description = 'Import Mafsil positions from a text file into mafsil_positions table';

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

    private static array $lastSurahIndexByJuz = [];
    private static array $wholeSurahCounterByJuz = [];

    public function handle(): int
    {
        $file = base_path($this->option('file'));

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $content = file_get_contents($file);
        $lines = preg_split('/\r\n|\r|\n/', $content);

        $fresh = (bool) $this->option('fresh');
        $nullifiedCount = 0;

        if ($fresh) {
            $nullifiedCount = \Illuminate\Support\Facades\DB::table('recitation_pages')
                ->whereNotNull('mafsil_position_id')
                ->update(['mafsil_position_id' => null]);
            if ($nullifiedCount > 0) {
                $this->warn("Nulled {$nullifiedCount} recitation_pages.mafsil_position_id references before delete.");
            }
            MafsilPosition::query()->delete();
        }

        $currentJuz = null;
        $listOrderByJuz = [];
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $failed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, 'الجزء')) {
                $currentJuz = $this->parseJuzHeader($line);
                if ($currentJuz) {
                    $this->info("Processing Juz {$currentJuz}");
                }
                continue;
            }

            if ($currentJuz === null) {
                continue;
            }

            $parsed = $this->parsePositionLine($line);
            if ($parsed === null) {
                $failed[] = ['juz' => $currentJuz, 'line' => $line, 'reason' => 'parse failed'];
                continue;
            }

            $listOrderByJuz[$currentJuz] = ($listOrderByJuz[$currentJuz] ?? 0) + 1;
            $listOrder = $listOrderByJuz[$currentJuz];

            $endAyahNumber = $parsed['end'] ?? null;
            if ($parsed['surah_number'] !== null) {
                $resolved = $this->resolveBySurahNumber(
                    $currentJuz,
                    $parsed['surah_number'],
                    $parsed['surah_end'] ?? $parsed['surah_number']
                );
            } elseif ($parsed['whole_surah']) {
                $resolved = $this->resolveWholeSurah($currentJuz);
            } else {
                if ($endAyahNumber === null) {
                    $endAyahNumber = $this->getLastAyahNumberInJuz($currentJuz);
                }
                $resolved = $this->resolveAyahs($currentJuz, $parsed['start'], $endAyahNumber);
                if ($resolved === null && $parsed['end'] !== null) {
                    $endAyahNumber = $this->clampEndAyahToJuz($currentJuz, $endAyahNumber);
                    $resolved = $this->resolveAyahs($currentJuz, $parsed['start'], $endAyahNumber);
                }
            }

            if ($resolved === null) {
                $failed[] = [
                    'juz' => $currentJuz,
                    'position' => $parsed['position'],
                    'line' => $line,
                    'reason' => 'could not resolve ayahs',
                ];
                continue;
            }

            $existing = MafsilPosition::query()
                ->where('juz_number', $currentJuz)
                ->where('list_order', $listOrder)
                ->first();

            $payload = [
                'juz_number' => $currentJuz,
                'position_number' => $parsed['position'],
                'list_order' => $listOrder,
                'start_ayah_id' => $resolved['start_ayah_id'],
                'end_ayah_id' => $resolved['end_ayah_id'],
                'difficulty' => $parsed['difficulty'],
                'notes' => $resolved['note'] ?? null,
            ];

            if ($existing) {
                $existing->fill($payload)->save();
                $updated++;
            } else {
                MafsilPosition::create($payload);
                $inserted++;
            }
        }

        // Delete any rows with list_order beyond what was imported for each Juz
        // (cleans up duplicates from previous runs). References in
        // recitation_pages are nullified first.
        $staleDeleted = 0;
        $nullifiedForCleanup = 0;
        foreach ($listOrderByJuz as $juzNum => $maxOrder) {
            $staleIds = MafsilPosition::query()
                ->where('juz_number', $juzNum)
                ->where('list_order', '>', $maxOrder)
                ->pluck('id')
                ->all();
            if (empty($staleIds)) {
                continue;
            }

            $nullifiedForCleanup += \Illuminate\Support\Facades\DB::table('recitation_pages')
                ->whereIn('mafsil_position_id', $staleIds)
                ->update(['mafsil_position_id' => null]);

            $staleDeleted += MafsilPosition::query()
                ->whereIn('id', $staleIds)
                ->delete();
        }

        $this->newLine();
        $this->info("Inserted {$inserted}, updated {$updated}, deleted {$staleDeleted} stale positions (nulled {$nullifiedForCleanup} page refs).");

        if (! empty($failed)) {
            $this->warn('Failed to import ' . count($failed) . ' positions:');
            foreach ($failed as $item) {
                $this->warn("  Juz {$item['juz']}: {$item['line']} — {$item['reason']}");
            }
        }

        return self::SUCCESS;
    }

private function parseJuzHeader(string $line): ?int
    {
        $rest = trim(substr($line, strlen('الجزء')));
        foreach (self::JUZ_ORDINALS as $ordinal => $number) {
            if ($rest === $ordinal || str_starts_with($rest, $ordinal . ' ') || str_starts_with($rest, $ordinal . '-')) {
                return $number;
            }
        }
        return null;
    }

    private function parsePositionLine(string $line): ?array
    {
        $pattern = '/الموضع\s+([\d\s]+)\s*من\s*(?:آية\s+)?(?:رقم\s+)?([\d\s]+)\s*إلى\s*(?:آية\s+)?([\d\s]*)\s*(?:\(([^)]*)\))?/u';

        if (preg_match($pattern, $line, $matches)) {
            $position = (int) $this->toWestern($matches[1]);
            $start = (int) $this->toWestern($matches[2]);
            $endRaw = trim($this->toWestern($matches[3]));
            $end = ($endRaw === '') ? null : (int) $endRaw;
            $difficultyText = trim($matches[4] ?? '');

            return [
                'position' => $position,
                'start' => $start,
                'end' => $end,
                'difficulty' => $this->parseDifficulty($difficultyText),
                'whole_surah' => false,
                'surah_number' => null,
            ];
        }

        $surahHintPattern = '/الموضع\s+([\d\s]+)\s*\[سورة\s*[:=]?\s*(\d+)(?:\s*-\s*(\d+))?\]\s*(?:\(([^)]*)\))?/u';
        if (preg_match($surahHintPattern, $line, $matches)) {
            $position = (int) $this->toWestern($matches[1]);
            $surahStart = (int) $matches[2];
            $surahEnd = isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : $surahStart;
            $difficultyText = trim($matches[4] ?? '');

            return [
                'position' => $position,
                'start' => 1,
                'end' => null,
                'difficulty' => $this->parseDifficulty($difficultyText),
                'whole_surah' => false,
                'surah_number' => $surahStart,
                'surah_end' => $surahEnd,
            ];
        }

        $wholeSurahPattern = '/الموضع\s+([\d\s]+)\s*(?:\(([^)]*)\))?/u';
        if (preg_match($wholeSurahPattern, $line, $matches)) {
            $position = (int) $this->toWestern($matches[1]);
            $difficultyText = trim($matches[2] ?? '');

            return [
                'position' => $position,
                'start' => 1,
                'end' => null,
                'difficulty' => $this->parseDifficulty($difficultyText),
                'whole_surah' => true,
                'surah_number' => null,
            ];
        }

        return null;
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

    private function resolveAyahs(int $juzNumber, int $startAyahNumber, int $endAyahNumber): ?array
    {
        $ayahsBySurah = Ayah::query()
            ->with('surah')
            ->where('juz_number', $juzNumber)
            ->orderBy('id')
            ->get()
            ->groupBy('surah_id')
            ->sortKeys()
            ->values();

        if ($ayahsBySurah->isEmpty()) {
            return null;
        }

        // Build list of surahs with their ayah numbers in Juz order
        $surahs = [];
        foreach ($ayahsBySurah as $group) {
            $ayahNumbers = $group->pluck('ayah_number')->all();
            $ayahByNumber = $group->keyBy('ayah_number');
            $surahs[] = [
                'surah_id' => $group->first()->surah_id,
                'surah_number' => $group->first()->surah->surah_number,
                'ayah_numbers' => $ayahNumbers,
                'ayah_by_number' => $ayahByNumber,
                'min' => min($ayahNumbers),
                'max' => max($ayahNumbers),
            ];
        }

        $startIndex = self::$lastSurahIndexByJuz[$juzNumber] ?? 0;

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

        // Determine end surah
        $endSurahIndex = $this->findSurahForAyah($surahs, $endAyahNumber, $startSurahIndex);
        $note = null;

        // If start > end, the range likely crosses into the next surah
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

        if ($endAyah->id < $startAyah->id) {
            $note = 'Range appears invalid: end ayah precedes start ayah';
        }

        self::$lastSurahIndexByJuz[$juzNumber] = $endSurahIndex;

        return [
            'start_ayah_id' => $startAyah->id,
            'end_ayah_id' => $endAyah->id,
            'note' => $note,
        ];
    }

    private function clampEndAyahToJuz(int $juzNumber, int $endAyahNumber): int
    {
        $lastAyah = $this->getLastAyahInJuz($juzNumber);

        if ($lastAyah === null) {
            return $endAyahNumber;
        }

        // If the parsed end number equals the last ayah of the juz + 1, and that next ayah
        // is on the following page, clamp it to the last ayah of the juz.
        if ($endAyahNumber > $lastAyah->ayah_number) {
            return $lastAyah->ayah_number;
        }

        return $endAyahNumber;
    }

    private function getLastAyahInJuz(int $juzNumber): ?Ayah
    {
        return Ayah::query()
            ->where('juz_number', $juzNumber)
            ->orderByDesc('id')
            ->first();
    }

    private function getLastAyahNumberInJuz(int $juzNumber): int
    {
        $last = $this->getLastAyahInJuz($juzNumber);
        return $last?->ayah_number ?? 999;
    }

    private function resolveBySurahNumber(int $juzNumber, int $surahNumber, int $surahEndNumber = null): ?array
    {
        $surahEndNumber = $surahEndNumber ?? $surahNumber;

        $startAyahs = Ayah::query()
            ->with('surah')
            ->whereHas('surah', function ($q) use ($surahNumber) {
                $q->where('surah_number', $surahNumber);
            })
            ->where('juz_number', $juzNumber)
            ->orderBy('ayah_number')
            ->get();

        $endAyahs = Ayah::query()
            ->with('surah')
            ->whereHas('surah', function ($q) use ($surahEndNumber) {
                $q->where('surah_number', $surahEndNumber);
            })
            ->where('juz_number', $juzNumber)
            ->orderByDesc('ayah_number')
            ->get();

        if ($startAyahs->isEmpty() || $endAyahs->isEmpty()) {
            return null;
        }

        $firstAyah = $startAyahs->first();
        $lastAyah = $endAyahs->first();

        $endSurahIndex = $this->getSurahIndexInJuz($juzNumber, $lastAyah->surah_id);
        if ($endSurahIndex !== null) {
            self::$lastSurahIndexByJuz[$juzNumber] = $endSurahIndex;
        }

        $note = null;
        if ($lastAyah->id < $firstAyah->id) {
            $note = 'Range appears invalid: end ayah precedes start ayah';
        }

        return [
            'start_ayah_id' => $firstAyah->id,
            'end_ayah_id' => $lastAyah->id,
            'note' => $note,
        ];
    }

    private function getSurahIndexInJuz(int $juzNumber, int $surahId): ?int
    {
        $surahs = Ayah::query()
            ->where('juz_number', $juzNumber)
            ->orderBy('id')
            ->get()
            ->groupBy('surah_id')
            ->sortKeys()
            ->keys()
            ->values()
            ->all();

        $index = array_search($surahId, $surahs, true);
        return $index === false ? null : (int) $index;
    }

    private function resolveWholeSurah(int $juzNumber): ?array
    {
        $ayahsBySurah = Ayah::query()
            ->with('surah')
            ->where('juz_number', $juzNumber)
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
                'min' => min($ayahNumbers),
                'max' => max($ayahNumbers),
            ];
        }

        $counter = self::$wholeSurahCounterByJuz[$juzNumber] ?? null;
        if ($counter === null) {
            $counter = (self::$lastSurahIndexByJuz[$juzNumber] ?? -1) + 1;
        }
        if ($counter >= count($surahs)) {
            $counter = count($surahs) - 1;
        }
        $surah = $surahs[$counter];
        self::$wholeSurahCounterByJuz[$juzNumber] = $counter + 1;
        self::$lastSurahIndexByJuz[$juzNumber] = $counter;

        $firstAyah = Ayah::query()
            ->where('surah_id', $surah['surah_id'])
            ->where('ayah_number', $surah['min'])
            ->first();
        $lastAyah = Ayah::query()
            ->where('surah_id', $surah['surah_id'])
            ->where('ayah_number', $surah['max'])
            ->first();

        if ($firstAyah === null || $lastAyah === null) {
            return null;
        }

        return [
            'start_ayah_id' => $firstAyah->id,
            'end_ayah_id' => $lastAyah->id,
            'note' => null,
        ];
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
}
