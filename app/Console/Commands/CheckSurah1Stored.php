<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSurah1Stored extends Command
{
    protected $signature = 'check:surah1stored';

    public function handle(): int
    {
        $this->info('Checking all records with surah_number in range 1-2...');

        // Check all records with surah_number 1 or 2
        $surah1Records = DB::table('word_concordance')
            ->whereBetween('surah_number', [1, 2])
            ->orderBy('id')
            ->limit(20)
            ->get();

        $this->info('Records with surah 1-2: ' . $surah1Records->count());
        foreach ($surah1Records as $r) {
            $this->line("ID {$r->id}: surah={$r->surah_number}, ayah={$r->ayah_number}, pos={$r->word_position}: {$r->word_text}");
        }

        // Check if maybe surah is stored differently
        $minMax = DB::table('word_concordance')
            ->select(DB::raw('MIN(surah_number) as min_surah, MAX(surah_number) as max_surah'))
            ->first();

        $this->info('Surah range in DB: ' . $minMax->min_surah . ' to ' . $minMax->max_surah);

        // Check ayah numbers for surah 2
        $surah2Ayahs = DB::table('word_concordance')
            ->where('surah_number', 2)
            ->select('ayah_number')
            ->distinct()
            ->orderBy('ayah_number')
            ->limit(10)
            ->get();

        $this->info('First 10 ayahs in Surah 2: ' . $surah2Ayahs->pluck('ayah_number')->implode(', '));

        // Check if there's surah 0 or something unusual
        $unusual = DB::table('word_concordance')
            ->where('surah_number', '<', 1)
            ->orWhere('surah_number', '>', 114)
            ->count();

        $this->info('Records with unusual surah_number: ' . $unusual);

        return self::SUCCESS;
    }
}