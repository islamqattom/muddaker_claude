<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugSurah1Import extends Command
{
    protected $signature = 'debug:surah1';

    public function handle(): int
    {
        $this->info('Debugging Surah 1 import issue...');

        $url = 'https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/quran-morphology.txt';

        $context = stream_context_create([
            'http' => [
                'timeout' => 60,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);

        $lines = @file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES, $context);

        if (!$lines) {
            $this->error('Failed to download file');
            return self::FAILURE;
        }

        $this->info('Total lines in file: ' . count($lines));

        // Parse first data line (line 57, index 57)
        $line57 = trim($lines[57]);
        $this->info('Line 57: ' . $line57);

        $parts = explode("\t", $line57);
        $this->info('Parts count: ' . count($parts));

        if (count($parts) >= 1) {
            $location = trim($parts[0], '()');
            $this->info('Location: ' . $location);

            $locationParts = explode(':', $location);
            $this->info('Location parts: ' . json_encode($locationParts));

            if (count($locationParts) >= 4) {
                $this->info('surah=' . $locationParts[0] . ', ayah=' . $locationParts[1] . ', word=' . $locationParts[2] . ', sub=' . $locationParts[3]);
            }
        }

        // Check lines 0-60
        $this->info('');
        $this->info('First 65 lines of file:');
        for ($i = 0; $i < min(65, count($lines)); $i++) {
            $line = trim($lines[$i]);
            if (!empty($line)) {
                $parts = explode("\t", $line);
                $location = trim($parts[0], '()');
                $wordPart = isset($parts[1]) ? $parts[1] : '';
                $this->line("Line $i: $location -> $wordPart");
            }
        }

        // Now check what we have in DB for surah 1
        $this->info('');
        $surah1 = DB::table('word_concordance')
            ->where('surah_number', 1)
            ->orderBy('ayah_number')
            ->orderBy('word_position')
            ->get();

        $this->info('Surah 1 in DB: ' . $surah1->count() . ' records');

        if ($surah1->count() > 0) {
            $this->info('First 10 records:');
            foreach ($surah1->take(10) as $r) {
                $this->line("  ayah={$r->ayah_number}, pos={$r->word_position}: {$r->word_text}");
            }
        } else {
            $this->info('No Surah 1 records found!');

            // Check if data is stored under different surah number
            $surah0 = DB::table('word_concordance')
                ->where('surah_number', '<', 2)
                ->count();
            $this->info('Records with surah < 2: ' . $surah0);

            // Check for any surah number issues
            $surahCounts = DB::table('word_concordance')
                ->select('surah_number', DB::raw('count(*) as cnt'))
                ->groupBy('surah_number')
                ->orderBy('surah_number')
                ->limit(10)
                ->get();

            $this->info('First 10 surah counts:');
            foreach ($surahCounts as $sc) {
                $this->info("  Surah {$sc->surah_number}: {$sc->cnt} records");
            }
        }

        return self::SUCCESS;
    }
}