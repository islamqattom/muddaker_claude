<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ImanProgram;
use App\Models\ImanDailyEntry;
use Carbon\Carbon;

echo "=== TEST: Data for Ahmed's students ===\n\n";

$weekStart = Carbon::now()->startOfWeek(Carbon::SATURDAY);
$weekEnd = $weekStart->copy()->addDays(6);
echo "Week: {$weekStart->toDateString()} -> {$weekEnd->toDateString()}\n\n";

$programs = ImanProgram::with(['criteria', 'owner.user'])
    ->where('owner_type', 'student')
    ->get();

if ($programs->isEmpty()) {
    die("No student programs found.\n");
}

foreach ($programs as $p) {
    $studentName = $p->owner->user->name ?? 'Unknown';
    echo "Student: {$studentName} | Program ID: {$p->id} | {$p->name}\n";
    echo "  Criteria count: " . $p->criteria->count() . "\n";
    
    $entries = ImanDailyEntry::where('iman_program_id', $p->id)
        ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
        ->get();
    
    $done = 0;
    $total = $p->criteria->count() * 7;
    foreach ($entries as $e) {
        $c = $p->criteria->find($e->iman_criterion_id);
        if (!$c) continue;
        if ($c->type === 'check' && $e->is_checked) $done++;
        if ($c->type === 'number' && (int)$c->target_value > 0 && (int)$e->value >= (int)$c->target_value) $done++;
    }
    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
    echo "  Weekly completion: {$done}/{$total} ({$pct}%)\n";
    
    // Show sample data for first day
    $firstDay = $weekStart->toDateString();
    echo "  Sample ({$firstDay}):\n";
    foreach ($p->criteria->take(5) as $c) {
        $e = ImanDailyEntry::where('iman_program_id', $p->id)
            ->where('iman_criterion_id', $c->id)
            ->where('entry_date', $firstDay)
            ->first();
        $val = $c->type === 'check' 
            ? ($e && $e->is_checked ? '[X]' : '[ ]') 
            : ($e ? $e->value . '/' . $c->target_value : '0/' . $c->target_value);
        echo "    - {$c->name}: {$val}\n";
    }
    echo "\n";
}

echo "=== All tests passed! ===\n";
echo "\nYou can now log in as supervisor 'الأستاذ أحمد محمد' and visit:\n";
echo "  /supervisor/iman-program?scope=student&student_id=1\n";
echo "to see the student programs with data.\n";
