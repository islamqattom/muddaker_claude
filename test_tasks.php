<?php
require 'C:\xampp\htdocs\muddaker\vendor\autoload.php';
$app = require 'C:\xampp\htdocs\muddaker\bootstrap\app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MeetingTask;
use App\Models\Student;

$supervisorId = 2;
$supervisedStudentIds = Student::where('supervisor_id', $supervisorId)->pluck('id')->toArray();
echo "Supervised students: " . count($supervisedStudentIds) . " (IDs: " . implode(',', $supervisedStudentIds) . ")\n";

$tasks = MeetingTask::where(function ($q) use ($supervisorId, $supervisedStudentIds) {
    $q->where(function ($sub) use ($supervisorId) {
        $sub->where('assignee_type', 'user')->where('assignee_id', $supervisorId);
    });
    if (!empty($supervisedStudentIds)) {
        $q->orWhere(function ($sub) use ($supervisedStudentIds) {
            $sub->where('assignee_type', 'student')->whereIn('assignee_id', $supervisedStudentIds);
        });
    }
})->get();

echo "Total tasks for supervisor 2: " . $tasks->count() . "\n";
foreach ($tasks->take(3) as $t) {
    echo "  - ID {$t->id}: {$t->title} (due: {$t->due_date}, type: {$t->assignee_type}, id: {$t->assignee_id})\n";
}
