<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QuranApiService;

$svc = new QuranApiService();

echo "Testing searchVerse('بسم'):\n";
try {
    $results = $svc->searchVerse('بسم');
    echo "Results count: " . count($results) . "\n";
    if (count($results) > 0) {
        echo "First result: " . json_encode($results[0], JSON_UNESCAPED_UNICODE) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\nTesting searchVerse('الله'):\n";
try {
    $results = $svc->searchVerse('الله');
    echo "Results count: " . count($results) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}