<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSurah1 extends Command
{
    protected $signature = 'check:surah1';

    public function handle(): int
    {
        $this->info('Checking Surah 1 data...');

        // Check what ayah_numbers exist for surah 1
        $ayahs = DB::table('word_concordance')
            ->where('surah_number', 1)
            ->select('ayah_number')
            ->distinct()
            ->orderBy('ayah_number')
            ->get();

        $this->info('آيات سورة 1: ' . $ayahs->pluck('ayah_number')->implode(', '));

        // Check if maybe surah 1 is stored differently
        $allSurah1 = DB::table('word_concordance')
            ->where('surah_number', 1)
            ->get();

        $this->info('إجمالي سجلات سورة 1: ' . $allSurah1->count());

        // Check for any records with ayah_number 0 or unusual
        $unusual = DB::table('word_concordance')
            ->where('surah_number', 1)
            ->where('ayah_number', '<', 1)
            ->orWhere('ayah_number', '>', 7)
            ->get();

        $this->info('سورة 1 سجلات غير طبيعية: ' . $unusual->count());

        // Get some sample records from end of data
        $sample = DB::table('word_concordance')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $this->info('آخر السجلات:');
        foreach ($sample as $s) {
            $this->info("  ID {$s->id}: س{$s->surah_number} آ{$s->ayah_number} ك{$s->word_position} = {$s->word_text}");
        }

        return self::SUCCESS;
    }
}