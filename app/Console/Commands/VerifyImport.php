<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyImport extends Command
{
    protected $signature = 'verify:import';

    public function handle(): int
    {
        $this->info('Verifying import process...');

        // Download with Http and count lines
        $response = \Illuminate\Support\Facades\Http::timeout(60)
            ->get('https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/quran-morphology.txt');

        $body = $response->body();
        $lines = explode("\n", $body);
        $this->info('Http download lines (explode): ' . count($lines));

        // Download with file()
        $context = stream_context_create([
            'http' => ['timeout' => 60, 'user_agent' => 'Mozilla/5.0']
        ]);
        $fileLines = @file('https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/quran-morphology.txt',
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES, $context);
        $this->info('file() lines: ' . ($fileLines ? count($fileLines) : 'false'));

        // Show lines 0-5 from each
        $this->info('');
        $this->info('Http lines 0-5:');
        for ($i = 0; $i < min(6, count($lines)); $i++) {
            $this->line("  [$i]: " . substr(trim($lines[$i]), 0, 50));
        }

        if ($fileLines) {
            $this->info('file() lines 0-5:');
            for ($i = 0; $i < min(6, count($fileLines)); $i++) {
                $this->line("  [$i]: " . substr(trim($fileLines[$i]), 0, 50));
            }
        }

        // Check if the file has empty lines at start
        $this->info('');
        $firstContentLine = null;
        for ($i = 0; $i < min(60, count($lines)); $i++) {
            if (trim($lines[$i]) !== '') {
                $firstContentLine = $i;
                break;
            }
        }
        $this->info('First non-empty line index: ' . ($firstContentLine ?? 'none'));

        return self::SUCCESS;
    }
}