<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSurah1Missing extends Command
{
    protected $signature = 'check:missing';

    public function handle(): int
    {
        $this->info('Checking why Surah 1 is missing...');

        // Get the line count
        $url = 'https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/quran-morphology.txt';

        $context = stream_context_create([
            'http' => ['timeout' => 60, 'user_agent' => 'Mozilla/5.0']
        ]);

        $lines = @file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES, $context);

        if (!$lines) {
            $this->error('Failed to download');
            return self::FAILURE;
        }

        // Count how many lines have surah 1
        $surah1Lines = [];
        $firstSurah2Line = null;

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $parts = explode("\t", $line);
            $location = trim($parts[0], '()');
            $locationParts = explode(':', $location);

            if (count($locationParts) >= 1) {
                $surah = (int) $locationParts[0];
                if ($surah === 1) {
                    $surah1Lines[] = $i;
                } elseif ($firstSurah2Line === null && $surah === 2) {
                    $firstSurah2Line = $i;
                }
            }
        }

        $this->info('Total lines for Surah 1: ' . count($surah1Lines));
        $this->info('First Surah 2 line index: ' . $firstSurah2Line);

        // Show first few and last few lines of Surah 1
        $this->info('First 5 Surah 1 line indices: ' . implode(', ', array_slice($surah1Lines, 0, 5)));
        $this->info('Last 5 Surah 1 line indices: ' . implode(', ', array_slice($surah1Lines, -5)));

        // Check what line index our code starts at
        $this->info('');
        $this->info('Our code skips lines 0-56, so it starts at line 57');
        $this->info('Lines 0-56 contain header/copyright, not actual Quran data');
        $this->info('But file data actually starts at line 0 (first word is 1:1:1:1)');

        // The issue: we skip lines 0-56 thinking they're header
        // But actually lines 0-56 include Surah 1 data!
        $this->info('');
        $this->info('Checking line 57 specifically:');
        if (isset($lines[57])) {
            $parts = explode("\t", trim($lines[57]));
            $location = trim($parts[0], '()');
            $this->info("Line 57: $location -> {$parts[1]}");
        }

        return self::SUCCESS;
    }
}