<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the actual searchVerse method
$service = app(App\Services\QuranApiService::class);
try {
    $results = $service->searchVerse('بسم');
    echo "Results count: " . count($results) . "\n";
    if (count($results) > 0) {
        print_r($results[0]);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}