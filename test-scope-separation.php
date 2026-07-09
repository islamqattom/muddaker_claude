<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\ImanProgram;

$supervisor = User::where('role', 'supervisor')->first();
echo "Supervisor: {$supervisor->name} (ID: {$supervisor->id})\n\n";

// Self scope: programs owned by the supervisor (owner_type = 'user', owner_id = supervisor->id)
$selfPrograms = ImanProgram::where('owner_id', $supervisor->id)
    ->where('owner_type', $supervisor->getMorphClass())
    ->get();

echo "=== SELF SCOPE (Supervisor's own programs) ===\n";
echo "Count: " . $selfPrograms->count() . "\n";
foreach ($selfPrograms as $p) {
    echo "  - Program ID {$p->id}: '{$p->name}' (owner_type: {$p->owner_type})\n";
}

// Student scope: programs for a specific student
$student = Student::where('supervisor_id', $supervisor->id)->first();
echo "\n=== STUDENT SCOPE (Student: {$student->user->name}) ===\n";

$studentPrograms = ImanProgram::where('owner_id', $student->id)
    ->where('owner_type', $student->getMorphClass())
    ->get();

echo "Count: " . $studentPrograms->count() . "\n";
foreach ($studentPrograms as $p) {
    echo "  - Program ID {$p->id}: '{$p->name}' (owner_type: {$p->owner_type})\n";
}

echo "\n=== VERIFICATION ===\n";
echo "Self scope shows only supervisor's programs: " . ($selfPrograms->where('owner_type', 'user')->count() === $selfPrograms->count() ? 'PASS' : 'FAIL') . "\n";
echo "Student scope shows only student programs: " . ($studentPrograms->where('owner_type', 'student')->count() === $studentPrograms->count() ? 'PASS' : 'FAIL') . "\n";
echo "No overlap: " . ($selfPrograms->pluck('id')->intersect($studentPrograms->pluck('id'))->isEmpty() ? 'PASS' : 'FAIL') . "\n";
