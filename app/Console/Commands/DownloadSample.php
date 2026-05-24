<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DownloadSample extends Command
{
    protected $signature = 'download:sample';

    public function handle(): int
    {
        $this->info('Downloading sample of morphology file...');

        $response = Http::timeout(60)->get('https://raw.githubusercontent.com/mustafa0x/quran-morphology/master/quran-morphology.txt');

        if ($response->successful()) {
            $lines = explode("\n", $response->body());

            $this->info('Total lines in file: ' . count($lines));

            // Show header lines
            $this->info('Header lines (0-56):');
            for ($i = 0; $i < min(20, count($lines)); $i++) {
                $this->line("Line $i: " . substr($lines[$i], 0, 100));
            }

            $this->line('');
            $this->info('Line 57: ' . ($lines[57] ?? 'N/A'));
            $this->info('Line 58: ' . ($lines[58] ?? 'N/A'));
            $this->info('Line 59: ' . ($lines[59] ?? 'N/A'));
            $this->info('Line 60: ' . ($lines[60] ?? 'N/A'));
        }

        return self::SUCCESS;
    }
}