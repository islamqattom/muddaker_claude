<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\RecitationPage;
use App\Models\RecitationSession;
use App\Services\ReportService;

$studentId = 1;      // عبدالله أحمد الزيد
$supervisorId = 2;   // الأستاذ أحمد محمد
$studentName = 'عبدالله أحمد الزيد';
$mark = 'TEST_DATA';

// --- clean previous TEST_DATA runs (idempotent) ---
$oldIds = RecitationSession::where('student_id', $studentId)->where('notes', $mark)->pluck('id');
if ($oldIds->isNotEmpty()) {
    RecitationPage::whereIn('session_id', $oldIds)->delete();
    RecitationSession::whereIn('id', $oldIds)->delete();
}
Attendance::where('student_id', $studentId)->where('notes', $mark)->delete();
echo "Cleaned previous test rows.\n";

// --- 1) Attendance: verify الحضور KPI counts late as attendance ---
$attendance = [
    ['date' => '2026-09-09', 'status' => 'present', 'arrival_time' => '08:50:00'],
    ['date' => '2026-09-10', 'status' => 'late',    'arrival_time' => '09:25:00'],
    ['date' => '2026-09-14', 'status' => 'absent',  'arrival_time' => null],
    ['date' => '2026-09-15', 'status' => 'excused', 'arrival_time' => null],
];
foreach ($attendance as $a) {
    Attendance::updateOrCreate(
        ['student_id' => $studentId, 'date' => $a['date']],
        ['supervisor_id' => $supervisorId, 'status' => $a['status'], 'arrival_time' => $a['arrival_time'], 'notes' => $mark]
    );
}
echo "Attendance rows added: " . count($attendance) . "\n";

// helper: create a session with page rows
$makeSession = function (array $o) use ($studentId, $supervisorId, $studentName, $mark) {
    $pages = range($o['start'], $o['end']);
    $session = RecitationSession::create([
        'student_id'            => $studentId,
        'student_name'          => $studentName,
        'supervisor_id'         => $supervisorId,
        'type'                  => $o['type'],
        'part_id'               => $o['part_id'],
        'start_page'            => $o['start'],
        'end_page'              => $o['end'],
        'total_pages'           => count($pages),
        'total_errors'          => $o['errors'] ?? 0,
        'total_hesitations'     => 0,
        'status'                => $o['status'],
        'session_date'          => $o['date'],
        'notes'                 => $mark,
        'mafsil_type'           => $o['mafsil_type'] ?? null,
        'max_score'             => $o['max_score'] ?? null,
        'final_score'           => $o['final_score'] ?? null,
        'unmastered_pages_count'=> $o['unmastered_pages_count'] ?? null,
    ]);

    foreach ($pages as $p) {
        RecitationPage::create([
            'session_id'   => $session->id,
            'page_number'  => $p,
            'errors'       => 0,
            'hesitations'  => 0,
            'color_status' => $o['is_passed'] ?? true ? 'green' : 'black',
            'is_passed'    => $o['is_passed'] ?? true,
        ]);
    }

    return $session;
};

// --- 2) Memorized part that SHOULD count: part 10 (pages 182-201),
//        fully memorized 2026-08-05, سرد passed AFTER on 2026-08-20. ---
$makeSession(['type' => 'new_memorization', 'part_id' => 10, 'start' => 182, 'end' => 201, 'status' => 'passed', 'date' => '2026-08-05']);
$makeSession(['type' => 'recitation',       'part_id' => 10, 'start' => 182, 'end' => 201, 'status' => 'passed', 'date' => '2026-08-20', 'final_score' => 95]);

// --- 3) Part that should NOT count: part 11 (pages 202-221).
//        A سرد passed over only pages 202-211 on 2026-08-10 (BEFORE the
//        part was completed); the remaining pages are memorized 2026-09-10. ---
$makeSession(['type' => 'recitation',       'part_id' => 11, 'start' => 202, 'end' => 211, 'status' => 'passed', 'date' => '2026-08-10', 'final_score' => 90]);
$makeSession(['type' => 'new_memorization', 'part_id' => 11, 'start' => 202, 'end' => 221, 'status' => 'passed', 'date' => '2026-09-10']);

// --- 4) Incomplete sessions of every type: must be counted with the failing. ---
$incompleteTypes = [
    ['type' => 'new_memorization', 'part_id' => 12, 'start' => 222, 'end' => 224, 'status' => 'incomplete', 'date' => '2026-09-15'],
    ['type' => 'review',           'part_id' => 12, 'start' => 222, 'end' => 224, 'status' => 'incomplete', 'date' => '2026-09-15'],
    ['type' => 'unmastered',       'part_id' => 12, 'start' => 222, 'end' => 224, 'status' => 'incomplete', 'date' => '2026-09-15', 'unmastered_pages_count' => 3],
    ['type' => 'recitation',       'part_id' => 12, 'start' => 222, 'end' => 224, 'status' => 'incomplete', 'date' => '2026-09-15'],
    ['type' => 'mafsil',           'part_id' => 12, 'start' => 222, 'end' => 226, 'status' => 'incomplete', 'date' => '2026-09-15', 'mafsil_type' => 3, 'max_score' => 100, 'final_score' => 40],
];
foreach ($incompleteTypes as $o) {
    $makeSession($o);
}

// --- 5) A "لم يسمع" (not_recitate) session: must NOT be shown as مفصل. ---
RecitationSession::create([
    'student_id'   => $studentId,
    'student_name' => $studentName,
    'supervisor_id'=> $supervisorId,
    'type'         => 'not_recitate',
    'status'       => 'not_recitate',
    'session_date' => '2026-09-16',
    'notes'        => 'TEST_DATA - لم يسمع',
]);
echo "Recitation sessions added: 4 (parts 10/11) + " . count($incompleteTypes) . " incomplete + 1 not_recitate = " . (5 + count($incompleteTypes)) . "\n";

// --- Verify through the report service (year period, like the admin page) ---
$reports = app(ReportService::class);
$sessions = RecitationSession::where('student_id', $studentId)
    ->with(['student.user', 'part', 'pages'])
    ->whereBetween('session_date', ['2026-01-01', '2026-12-31'])
    ->get();
$attendances = Attendance::where('student_id', $studentId)->whereBetween('date', ['2026-01-01', '2026-12-31'])->get();

$bd = $reports->studentBreakdown($sessions, $attendances, \Carbon\Carbon::parse('2026-12-31'))[0];
$rec = $reports->recitationStats($sessions);
$att = $reports->attendanceStats($attendances);

echo "\n=== Report after adding test data (year 2026) ===\n";
echo "Memorized parts (عدد الأجزاء المحفوظة): {$bd['new_memorization_parts']}  (expected 2: part 1 + part 10)\n";
echo "New-mem sessions: {$bd['new_memorization_sessions']} | passed: {$bd['new_memorization_passed']} | failed: {$bd['new_memorization_failed']}\n";
echo "Review sessions: {$bd['review_sessions']} | passed: {$bd['review_passed']} | failed: {$bd['review_failed']}\n";
echo "Unmastered sessions: {$bd['unmastered_sessions']} | passed: {$bd['unmastered_passed']} | failed: {$bd['unmastered_failed']}\n";
echo "Recitation (سرد) sessions: {$bd['recitation_sessions']} | passed: {$bd['recitation_passed']} | failed: {$bd['recitation_failed']}\n";
echo "Mafsil sessions: {$bd['mafsil_sessions']}\n";
echo "Recitation stats -> total: {$rec['total']} | passed: {$rec['passed']} | failed: {$rec['failed']} | pass_rate: {$rec['pass_rate']}%\n";
echo "Attendance -> total: {$att['total']} | الحضور(present+late): {$att['attendance']} | present: {$att['present']} | late: {$att['late']} | absent: {$att['absent']} | excused: {$att['excused']} | rate: {$att['attendance_rate']}%\n";