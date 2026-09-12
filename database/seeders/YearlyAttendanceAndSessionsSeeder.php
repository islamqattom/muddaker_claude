<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class YearlyAttendanceAndSessionsSeeder extends Seeder
{
    /** @var array<int, int> */
    private array $studentPartOffset = [];

    public function run(): void
    {
        $students = Student::with('user')->orderBy('id')->limit(20)->get();
        if ($students->isEmpty()) {
            $this->command?->warn('No students found, skipping.');
            return;
        }

        $parts = QuranPart::orderBy('part_number')->get();
        if ($parts->isEmpty()) {
            $this->command?->warn('No Quran parts found, skipping.');
            return;
        }

        $end = Carbon::now()->startOfDay();
        $start = (clone $end)->subDays(364); // ~365 days inclusive

        // Random but deterministic so re-runs are stable
        mt_srand(20250911);

        $existingAtt = Attendance::where('date', '>=', $start->toDateString())
            ->get(['student_id', 'date'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('date')->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : (string) $d)->all())
            ->all();
        $existingRec = RecitationSession::where('session_date', '>=', $start->toDateString())
            ->get(['student_id', 'session_date'])
            ->groupBy(fn ($r) => $r->student_id . '|' . ($r->session_date instanceof Carbon ? $r->session_date->toDateString() : (string) $r->session_date))
            ->all();

        $attendanceCreated = 0;
        $sessionsCreated = 0;

        foreach ($students as $idx => $student) {
            $this->studentPartOffset[$student->id] = ($idx % 5) + 1;
            $existingDates = $existingAtt[$student->id] ?? [];

            for ($d = (clone $start); $d->lte($end); $d->addDay()) {
                $dateStr = $d->toDateString();

                // Friday off (Islamic weekend)
                if ($d->isFriday()) {
                    continue;
                }

                // Pick a status with weighted distribution
                $roll = mt_rand(1, 100);
                $status = match (true) {
                    $roll <= 70 => 'present',
                    $roll <= 80 => 'late',
                    $roll <= 90 => 'absent',
                    $roll <= 95 => 'excused',
                    default     => 'holiday',
                };

                // Insert attendance (skip duplicates)
                if (! in_array($dateStr, $existingDates, true)) {
                    Attendance::create([
                        'student_id' => $student->id,
                        'supervisor_id' => $student->supervisor_id,
                        'date' => $dateStr,
                        'status' => $status,
                        'arrival_time' => $status === 'late' ? $this->randomLateTime() : ($status === 'present' ? $this->randomOnTime() : null),
                        'scheduled_time' => '16:00:00',
                        'notes' => null,
                    ]);
                    $attendanceCreated++;
                }

                // Skip recitation sessions for non-present days
                if (! in_array($status, ['present', 'late'], true)) {
                    continue;
                }

                // Skip if already had a session this day
                $sessionKey = $student->id . '|' . $dateStr;
                if (isset($existingRec[$sessionKey])) {
                    continue;
                }

                // 1-2 sessions per present day
                $sessionsToday = mt_rand(1, 2);
                for ($s = 0; $s < $sessionsToday; $s++) {
                    $part = $parts[($this->studentPartOffset[$student->id] + $idx + $s + $d->dayOfYear) % $parts->count()];

                    // Pick a type weighted toward review (most common in real life)
                    $typeRoll = mt_rand(1, 100);
                    $type = match (true) {
                        $typeRoll <= 60 => 'review',
                        $typeRoll <= 80 => 'new_memorization',
                        $typeRoll <= 90 => 'recitation',
                        $typeRoll <= 95 => 'unmastered',
                        default         => 'mafsil',
                    };

                    // Pick status
                    $sRoll = mt_rand(1, 100);
                    $sStatus = match (true) {
                        $sRoll <= 75 => 'passed',
                        $sRoll <= 90 => 'failed',
                        default      => 'failed_continued',
                    };

                    $errors = mt_rand(0, $type === 'mafsil' ? 8 : 5);
                    $hesitations = mt_rand(0, 8);
                    $pages = mt_rand(2, 5);
                    $startPage = mt_rand($part->start_page, max($part->start_page, $part->end_page - $pages));
                    $endPage = min($part->end_page, $startPage + $pages - 1);

                    RecitationSession::create([
                        'student_id' => $student->id,
                        'student_name' => $student->user->name ?? '',
                        'mahd_name' => null,
                        'supervisor_id' => $student->supervisor_id,
                        'type' => $type,
                        'part_id' => $type === 'mafsil' ? null : $part->id,
                        'start_page' => $startPage,
                        'end_page' => $endPage,
                        'total_pages' => $pages,
                        'total_errors' => $errors,
                        'total_hesitations' => $hesitations,
                        'status' => $sStatus,
                        'mafsil_type' => $type === 'mafsil' ? [3, 6, 10, 15, 20, 25, 30][mt_rand(0, 5)] : null,
                        'max_score' => $type === 'mafsil' ? 90.0 : null,
                        'final_score' => round(mt_rand(60, 100) + mt_rand(0, 99) / 100, 2),
                        'session_date' => $dateStr,
                        'notes' => null,
                    ]);
                    $sessionsCreated++;
                }
            }
        }

        $this->command?->info("Created {$attendanceCreated} attendance records and {$sessionsCreated} recitation sessions.");
    }

    private function randomOnTime(): string
    {
        $h = mt_rand(15, 16);
        $m = mt_rand(0, 45);
        return sprintf('%02d:%02d:00', $h, $m);
    }

    private function randomLateTime(): string
    {
        $h = mt_rand(16, 18);
        $m = mt_rand(5, 55);
        return sprintf('%02d:%02d:00', $h, $m);
    }
}
