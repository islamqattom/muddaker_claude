<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Models\WarningDraft;
use App\Models\WarningRule;
use App\Notifications\WarningDraftCreated;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluateWarningRules extends Command
{
    protected $signature = 'warning:evaluate-rules {--rule= : Specific rule ID to evaluate}';

    protected $description = 'Evaluate all active warning rules and create drafts for triggering students';

    public function handle(): int
    {
        $query = WarningRule::active();
        if ($this->option('rule')) {
            $query->where('id', $this->option('rule'));
        }
        $rules = $query->get();

        $this->info("Evaluating {$rules->count()} rule(s)...");

        $createdCount = 0;

        foreach ($rules as $rule) {
            $results = $this->evaluateRule($rule);
            $createdCount += $results;
        }

        $this->info("Done. Created {$createdCount} draft(s).");
        return Command::SUCCESS;
    }

    protected function evaluateRule(WarningRule $rule): int
    {
        $toDate = Carbon::today();
        $fromDate = $toDate->copy()->subDays($rule->period_days);
        $createdCount = 0;

        if ($rule->condition_type === 'absent_days') {
            $triggeringStudents = $this->getStudentsWithAbsentDays($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } elseif ($rule->condition_type === 'late_days') {
            $triggeringStudents = $this->getStudentsWithLateDays($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } elseif ($rule->condition_type === 'failed_sessions') {
            $triggeringStudents = $this->getStudentsWithFailedSessions($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } elseif ($rule->condition_type === 'not_recitate_count') {
            $triggeringStudents = $this->getStudentsWithNotRecitate($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } else {
            return 0;
        }

        foreach ($triggeringStudents as $studentData) {
            $student = Student::find($studentData['student_id']);
            if (!$student || !$student->supervisor_id) {
                $this->warn("  Skipped student ID {$studentData['student_id']}: not found or no supervisor assigned");
                continue;
            }

            // Skip if pending/approved draft already exists for this rule+student
            $existingDraft = WarningDraft::where('warning_rule_id', $rule->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingDraft) {
                continue;
            }

            // Skip if dismissed draft from same rule within the same period
            $recentlyDismissed = WarningDraft::where('warning_rule_id', $rule->id)
                ->where('student_id', $student->id)
                ->where('status', 'dismissed')
                ->where('created_at', '>=', $fromDate)
                ->exists();

            if ($recentlyDismissed) {
                continue;
            }

            $attendanceData = [];
            $recitationData = [];

            if ($rule->isAttendance()) {
                $attendanceData = $this->fetchAttendanceData($student, $fromDate, $toDate, $rule->condition_type);
            }

            if ($rule->isRecitation()) {
                $recitationData = $this->fetchRecitationData($student, $fromDate, $toDate, $rule->condition_type);
            }

            $title = $this->generateTitle($rule, $student, $studentData['count']);
            $content = $this->generateContent($rule, $student, $studentData['count'], $fromDate, $toDate);

            $draft = WarningDraft::create([
                'warning_rule_id' => $rule->id,
                'student_id' => $student->id,
                'supervisor_id' => $student->supervisor_id,
                'status' => 'pending',
                'title' => $title,
                'content' => $content,
                'issue_date' => $toDate,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'attendance_data' => $attendanceData,
                'recitation_data' => $recitationData,
                'triggered_value' => $studentData['count'],
                'triggered_threshold' => $rule->threshold,
            ]);

            // Notify supervisor
            $supervisor = User::find($student->supervisor_id);
            if ($supervisor) {
                try {
                    $supervisor->notify(new WarningDraftCreated($draft));
                } catch (\Exception $e) {
                    Log::error("Failed to notify supervisor {$supervisor->id} about warning draft {$draft->id} for rule {$rule->id}: " . $e->getMessage());
                }
            }

            $createdCount++;
            $this->line("  Created draft for student {$student->name} (triggered: {$studentData['count']})");
        }

        return $createdCount;
    }

    protected function getStudentsWithAbsentDays(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = Attendance::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'absent')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function getStudentsWithLateDays(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = Attendance::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'late')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function getStudentsWithFailedSessions(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = RecitationSession::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'failed')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function getStudentsWithNotRecitate(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = RecitationSession::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'not_recitate')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function fetchAttendanceData(Student $student, Carbon $fromDate, Carbon $toDate, string $conditionType): array
    {
        $statusMap = [
            'absent_days' => ['absent', 'late', 'excused'],
            'late_days' => ['late'],
        ];

        $statuses = $statusMap[$conditionType] ?? ['absent'];

        return Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereIn('status', $statuses)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'day' => $a->day,
                'date' => $a->date,
                'status' => $a->status,
                'arrival_time' => $a->arrival_time,
                'notes' => $a->notes,
            ])
            ->toArray();
    }

    protected function fetchRecitationData(Student $student, Carbon $fromDate, Carbon $toDate, string $conditionType): array
    {
        $statusMap = [
            'failed_sessions' => ['failed', 'not_recitate'],
            'not_recitate_count' => ['not_recitate'],
        ];

        $statuses = $statusMap[$conditionType] ?? ['failed'];

        return RecitationSession::where('student_id', $student->id)
            ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereIn('status', $statuses)
            ->with(['pages'])
            ->orderBy('session_date', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'day' => $s->day,
                'session_date' => $s->session_date,
                'type' => $s->type,
                'status' => $s->status,
                'total_pages' => $s->total_pages,
                'passed_pages' => $s->passed_pages ?? 0,
                'failed_pages' => $s->failed_pages ?? 0,
                'notes' => $s->notes,
            ])
            ->toArray();
    }

    protected function generateTitle(WarningRule $rule, Student $student, int $count): string
    {
        $typeLabels = [
            'attendance' => 'إنذار غياب/تأخير',
            'recitation' => 'إنذار تقييم حفظ',
            'general' => 'إنذار عام',
        ];
        return $typeLabels[$rule->type] ?? 'إنذار';
    }

    protected function generateContent(WarningRule $rule, Student $student, int $count, Carbon $fromDate, Carbon $toDate): string
    {
        $fromFormatted = $fromDate->format('Y-m-d');
        $toFormatted = $toDate->format('Y-m-d');

        if ($rule->condition_type === 'absent_days') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، تغيب الطالب {$student->name} عن {$count} أيام من أصل الحضور المطلوب.";
        }
        if ($rule->condition_type === 'late_days') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، تأخر الطالب {$student->name} في الحضور {$count} مرات.";
        }
        if ($rule->condition_type === 'failed_sessions') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، لم يجتز الطالب {$student->name} {$count} حصص تقييم.";
        }
        if ($rule->condition_type === 'not_recitate_count') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، لم يسمع الطالب {$student->name} {$count} حصص.";
        }
        return "تم رصد حالة تستدعي إصدار إنذار للطالب {$student->name}.";
    }
}
