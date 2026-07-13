<?php
require 'C:\xampp\htdocs\muddaker\vendor\autoload.php';
$app = require 'C:\xampp\htdocs\muddaker\bootstrap\app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
$user = \App\Models\User::find(2);
Auth::login($user);

$content = view('admin.tasks.calendar', [
    'tasksJson' => [],
    'students' => collect(),
    'supervisors' => collect(),
    'studentFilter' => null,
    'selectedStudent' => null,
    'role' => 'supervisor',
])->withErrors(new \Illuminate\Support\MessageBag())->render();

file_put_contents('calendar_test.html', $content);
echo "Saved " . strlen($content) . " bytes\n";
echo "listWeek: " . (strpos($content, 'listWeek') !== false ? 'YES' : 'NO') . "\n";
echo "list-event: " . (strpos($content, 'fc-list-event') !== false ? 'YES' : 'NO') . "\n";
echo "customizeListEvent: " . (strpos($content, 'customizeListEvent') !== false ? 'YES' : 'NO') . "\n";
