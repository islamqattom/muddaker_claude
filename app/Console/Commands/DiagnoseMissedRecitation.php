<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Services\MissedRecitationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DiagnoseMissedRecitation extends Command
{
    protected $signature = 'recitation:diagnose {student : Student id} {date : Date (Y-m-d)}';

    protected $description = 'Explain why a student did or did not get an automatic "لم يسمع" session for a date';

    public function handle(MissedRecitationService $service): int
    {
        $student = Student::with('user')->find($this->argument('student'));

        if (! $student) {
            $this->error('Student not found.');

            return self::FAILURE;
        }

        $date = Carbon::parse($this->argument('date'));

        $attendance = Attendance::where('student_id', $student->id)->whereDate('date', $date)->first();
        $sessions = RecitationSession::withoutGlobalScope('exclude_visitor_mafsil')
            ->where('student_id', $student->id)
            ->whereDate('session_date', $date)
            ->get();

        $this->info("Student: {$student->name} (#{$student->id}) — {$date->toDateString()}");
        $this->line('Attendance status: ' . ($attendance->status ?? '—'));

        if ($attendance) {
            $this->line('Required recitation day: ' . ($service->isRequiredAttendanceDay($attendance) ? 'yes' : 'no'));
            $this->line('Day has ended: ' . ($service->hasAttendanceDayEnded($attendance, now()) ? 'yes' : 'no'));
            $this->line('Has a session for the day: ' . ($sessions->isNotEmpty() ? 'yes' : 'no'));
            $this->line('Would create now: ' . ($service->shouldCreateForAttendance($attendance, now()) ? 'yes' : 'no'));
        } else {
            $this->line('No attendance record → the rule cannot fire.');
        }

        foreach ($sessions as $session) {
            $this->line(sprintf(
                'Session #%d: type=%s status=%s auto=%s',
                $session->id,
                $session->type,
                $session->status,
                $session->auto_generated ? 'yes' : 'no'
            ));
        }

        return self::SUCCESS;
    }
}
