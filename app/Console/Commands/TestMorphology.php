<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WordConcordance;

class TestMorphology extends Command
{
    protected $signature = 'test:morphology';

    public function handle(): int
    {
        $this->info('Testing WordConcordance data...');

        // Check the structure - get unique surah numbers
        $surahs = WordConcordance::select('surah_number')->distinct()->orderBy('surah_number')->get();
        $this->info('عدد السور: ' . $surahs->count());
        $this->info('السور: ' . $surahs->pluck('surah_number')->implode(', '));

        // Check if Surah 1 has any data
        $surah1 = WordConcordance::where('surah_number', 1)->count();
        $this->info('سورة 1: ' . $surah1 . ' كلمة');

        // Check distribution
        $distribution = WordConcordance::select('surah_number', \DB::raw('count(*) as count'))
            ->groupBy('surah_number')
            ->orderBy('surah_number')
            ->get();
        $this->info('التوزيع:');
        foreach ($distribution as $d) {
            $this->info("  سورة {$d->surah_number}: {$d->count} كلمة");
        }

        return self::SUCCESS;
    }
}