<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$s = app(App\Services\QuranApiService::class);
try {
    $r = $s->searchVerse('بسم');
    print_r($r);
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}