<?php
require 'C:\xampp\htdocs\muddaker\vendor\autoload.php';
$app = require 'C:\xampp\htdocs\muddaker\bootstrap\app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

fwrite(STDERR, "DB: ".\Config::get('database.default')."\n");

$count = \App\Models\Surah::count();
fwrite(STDERR, "Total surahs: $count\n");

$countAyah = \App\Models\Ayah::count();
fwrite(STDERR, "Total ayahs: $countAyah\n");

$countPos = \App\Models\MafsilPosition::count();
fwrite(STDERR, "Total mafsils: $countPos\n");

$pos = \App\Models\MafsilPosition::with(['startAyah.surah', 'endAyah.surah'])->first();
if ($pos) {
    fwrite(STDERR, "Sample mafsils:\n");
    fwrite(STDERR, " - id: ".$pos->id."\n");
    fwrite(STDERR, " - start_ayah_id: ".$pos->start_ayah_id."\n");
    fwrite(STDERR, " - end_ayah_id: ".$pos->end_ayah_id."\n");
    if ($pos->startAyah) {
        fwrite(STDERR, " - start ayah number: ".$pos->startAyah->ayah_number."\n");
        if ($pos->startAyah->surah) {
            fwrite(STDERR, " - start surah name: ".$pos->startAyah->surah->name_ar."\n");
        } else {
            fwrite(STDERR, " - start surah: NULL\n");
        }
    } else {
        fwrite(STDERR, " - startAyah: NULL\n");
    }
    fwrite(STDERR, " - positionRangeLabel: ".$pos->positionRangeLabel()."\n");
    fwrite(STDERR, " - pageNumberLabel: ".$pos->pageNumberLabel()."\n");
}
