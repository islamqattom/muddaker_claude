<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\ImanProgram;
use App\Models\ImanCriterion;
use App\Models\ImanDailyEntry;
use Carbon\Carbon;

$supervisor = User::where('name', 'like', '%ahmed%')
    ->orWhere('name', 'like', '%أحمد%')
    ->where('role', 'supervisor')
    ->first();

if (!$supervisor) {
    echo "No supervisor named 'ahmed' found. Looking for any supervisor...\n";
    $supervisor = User::where('role', 'supervisor')->first();
}

if (!$supervisor) {
    die("No supervisor found in the system.\n");
}

echo "Supervisor: {$supervisor->name} (ID: {$supervisor->id})\n";

$students = Student::with('user')
    ->where('supervisor_id', $supervisor->id)
    ->get();

if ($students->isEmpty()) {
    die("No students found for supervisor {$supervisor->name}.\n");
}

echo "Found {$students->count()} student(s):\n";
foreach ($students as $s) {
    echo "  - {$s->user->name} (Student ID: {$s->id})\n";
}

$weekStart = Carbon::now()->startOfWeek(Carbon::SATURDAY);
echo "\nWeek start: {$weekStart->toDateString()}\n";

foreach ($students as $student) {
    echo "\n--- Processing student: {$student->user->name} ---\n";
    
    $existing = ImanProgram::where('owner_id', $student->id)
        ->where('owner_type', $student->getMorphClass())
        ->count();
    
    if ($existing > 0) {
        echo "  Already has {$existing} program(s). Skipping creation.\n";
        $programs = ImanProgram::with('criteria')
            ->where('owner_id', $student->id)
            ->where('owner_type', $student->getMorphClass())
            ->get();
    } else {
        $program = ImanProgram::create([
            'name' => 'البرنامج الإيماني الأسبوعي',
            'description' => 'برنامج متابعة الالتزام اليومي',
            'created_by' => $supervisor->id,
            'owner_id' => $student->id,
            'owner_type' => $student->getMorphClass(),
            'is_active' => true,
            'start_date' => $weekStart->toDateString(),
        ]);
        echo "  Created program ID: {$program->id}\n";

        $criteriaData = [
            ['name' => 'صلاة الفجر', 'type' => 'check', 'target_value' => null],
            ['name' => 'صلاة الظهر', 'type' => 'check', 'target_value' => null],
            ['name' => 'صلاة العصر', 'type' => 'check', 'target_value' => null],
            ['name' => 'صلاة المغرب', 'type' => 'check', 'target_value' => null],
            ['name' => 'صلاة العشاء', 'type' => 'check', 'target_value' => null],
            ['name' => 'قراءة القرآن (صفحات)', 'type' => 'number', 'target_value' => 5],
            ['name' => 'أذكار الصباح', 'type' => 'check', 'target_value' => null],
            ['name' => 'أذكار المساء', 'type' => 'check', 'target_value' => null],
            ['name' => 'الاستغفار (مرة)', 'type' => 'number', 'target_value' => 100],
            ['name' => 'الصلاة على النبي', 'type' => 'number', 'target_value' => 100],
        ];

        foreach ($criteriaData as $i => $cd) {
            $c = ImanCriterion::create([
                'iman_program_id' => $program->id,
                'name' => $cd['name'],
                'type' => $cd['type'],
                'target_value' => $cd['target_value'],
                'sort_order' => $i,
            ]);
            echo "    + Criterion: {$c->name} ({$c->type}" . ($c->target_value ? ", target: {$c->target_value}" : "") . ")\n";
        }

        $programs = collect([$program->load('criteria')]);
    }

    foreach ($programs as $program) {
        foreach ($program->criteria as $criterion) {
            for ($d = 0; $d < 7; $d++) {
                $date = $weekStart->copy()->addDays($d)->toDateString();
                $existing = ImanDailyEntry::where('iman_program_id', $program->id)
                    ->where('iman_criterion_id', $criterion->id)
                    ->where('entry_date', $date)
                    ->first();
                if ($existing) continue;

                if ($criterion->type === 'check') {
                    $isChecked = rand(0, 1);
                    ImanDailyEntry::create([
                        'iman_program_id' => $program->id,
                        'iman_criterion_id' => $criterion->id,
                        'entry_date' => $date,
                        'is_checked' => $isChecked,
                        'value' => null,
                    ]);
                } else {
                    $max = (int) $criterion->target_value;
                    $val = rand(0, $max);
                    ImanDailyEntry::create([
                        'iman_program_id' => $program->id,
                        'iman_criterion_id' => $criterion->id,
                        'entry_date' => $date,
                        'is_checked' => false,
                        'value' => $val,
                    ]);
                }
            }
        }
        $count = ImanDailyEntry::where('iman_program_id', $program->id)->count();
        echo "  Program '{$program->name}': {$count} daily entries added/present\n";
    }
}

echo "\n=== DONE ===\n";
echo "Login as supervisor: {$supervisor->name} and visit /supervisor/iman-program?scope=student&student_id={$students->first()->id}\n";
