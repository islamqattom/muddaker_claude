<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDataFormat extends Command
{
    protected $signature = 'check:format';

    public function handle(): int
    {
        $this->info('Checking data format...');

        // Check for records with ayah_number = 0
        $zeroAyah = DB::table('word_concordance')
            ->where('ayah_number', 0)
            ->limit(10)
            ->get();

        $this->info('سجلات مع ayah_number = 0: ' . $zeroAyah->count());
        foreach ($zeroAyah as $s) {
            $this->info("  س{$s->surah_number} آ{$s->ayah_number} ك{$s->word_position} = {$s->word_text}");
        }

        // Check what surah has records with ayah 0
        $surahsWithZero = DB::table('word_concordance')
            ->where('ayah_number', 0)
            ->select('surah_number')
            ->distinct()
            ->get();

        $this->info('سور مع ayah=0: ' . $surahsWithZero->pluck('surah_number')->implode(', '));

        // Get sample of non-standard data
        $weirdData = DB::table('word_concordance')
            ->where('surah_number', 1)
            ->orWhere(function($q) {
                $q->where('surah_number', '>', 114)
                  ->orWhere('ayah_number', '<', 1)
                  ->orWhere('ayah_number', '>', 286);
            })
            ->limit(10)
            ->get();

        $this->info('بيانات غير طبيعية: ' . $weirdData->count());
        foreach ($weirdData as $s) {
            $this->info("  س{$s->surah_number} آ{$s->ayah_number} ك{$s->word_position} = {$s->word_text}");
        }

        return self::SUCCESS;
    }
}