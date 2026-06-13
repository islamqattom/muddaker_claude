<?php

namespace App\Console\Commands;

use App\Models\Ayah;
use App\Models\MafsilPosition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMafsilPositions extends Command
{
    protected $signature = 'mafsil:import-positions {--file=database/data/mafsil_positions.txt : Path to the positions text file}';
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
        'الثاني عشر' => 12,
        'الثالث عشر' => 13,
        'الرابع عشر' => 14,
        'الخامس عشر' => 15,
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

    public function handle(): int
    {
        $file = base_path($this->option('file'));

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $content = file_get_contents($file);
        $lines = preg_split('/\r\n|\r|\n/', $content);

        MafsilPosition::query()->delete();

        $currentJuz = null;
        $listOrder = 0;
        $inserted = 0;
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

            $listOrder++;
            $endAyahNumber = $this->clampEndAyahToJuz($currentJuz, $parsed['end']);
            $resolved = $this->resolveAyahs($currentJuz, $parsed['start'], $endAyahNumber);

            if ($resolved === null) {
                $failed[] = [
                    'juz' => $currentJuz,
                    'position' => $parsed['position'],
                    'line' => $line,
                    'reason' => 'could not resolve ayahs',
                ];
                continue;
            }

            MafsilPosition::create([
                'juz_number' => $currentJuz,
                'position_number' => $parsed['position'],
                'list_order' => $listOrder,
                'start_ayah_id' => $resolved['start_ayah_id'],
                'end_ayah_id' => $resolved['end_ayah_id'],
                'difficulty' => $parsed['difficulty'],
                'notes' => $resolved['note'] ?? null,
            ]);

            $inserted++;
        }

        $this->newLine();
        $this->info("Inserted {$inserted} positions.");

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
        foreach (self::JUZ_ORDINALS as $ordinal => $number) {
            if (str_contains($line, 'الجزء ' . $ordinal)) {
                return $number;
            }
        }
        return null;
    }

    private function parsePositionLine(string $line): ?array
    {
        $pattern = '/الموضع\s+([\d\s]+)\s*من\s*(?:آية\s+)?(?:رقم\s+)?([\d\s]+)\s*إلى\s*(?:آية\s+)?([\d\s]+)\s*(?:\(([^)]*)\))?/u';

        if (! preg_match($pattern, $line, $matches)) {
            return null;
        }

        $position = (int) $this->toWestern($matches[1]);
        $start = (int) $this->toWestern($matches[2]);
        $end = (int) $this->toWestern($matches[3]);
        $difficultyText = trim($matches[4] ?? '');

        return [
            'position' => $position,
            'start' => $start,
            'end' => $end,
            'difficulty' => $this->parseDifficulty($difficultyText),
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

        static $lastSurahIndexByJuz = [];
        $startIndex = $lastSurahIndexByJuz[$juzNumber] ?? 0;

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

        $lastSurahIndexByJuz[$juzNumber] = $endSurahIndex;

        return [
            'start_ayah_id' => $startAyah->id,
            'end_ayah_id' => $endAyah->id,
            'note' => $note,
        ];
    }

    private function clampEndAyahToJuz(int $juzNumber, int $endAyahNumber): int
    {
        $lastAyah = Ayah::query()
            ->where('juz_number', $juzNumber)
            ->orderByDesc('id')
            ->first();

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
