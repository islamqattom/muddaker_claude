<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test the assignee_name accessor on student-type tasks ===" . PHP_EOL;

$studentTasks = \App\Models\MeetingTask::where('assignee_type', 'student')->take(5)->get();
foreach ($studentTasks as $task) {
    echo "Task ID: {$task->id}" . PHP_EOL;
    echo "  Title: {$task->title}" . PHP_EOL;
    echo "  Assignee ID: {$task->assignee_id}" . PHP_EOL;
    echo "  Assignee Type: {$task->assignee_type}" . PHP_EOL;
    echo "  Assignee Name: " . $task->assignee_name . PHP_EOL;

    $student = $task->assignee;
    echo "  Assignee Model Class: " . get_class($student) . PHP_EOL;

    if ($student instanceof \App\Models\Student) {
        echo "  Student user_id: " . $student->user_id . PHP_EOL;
        echo "  Student user->name: " . ($student->user?->name ?? 'NULL') . PHP_EOL;
        echo "  Student->name (via accessor): " . $student->name . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "=== Test controller output for a student task ===" . PHP_EOL;
$controller = new \App\Http\Controllers\Admin\TaskController();
$task = $studentTasks->first();
if ($task) {
    $reflection = new ReflectionMethod($controller, 'formatTaskForCalendar');
    $reflection->setAccessible(true);
    $output = $reflection->invoke($controller, $task);
    echo "JSON output for task {$task->id}:" . PHP_EOL;
    echo "  assigneeName in JSON: " . ($output['extendedProps']['assigneeName'] ?? 'NOT SET') . PHP_EOL;
}
