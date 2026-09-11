<?php

namespace App\Console\Commands;

use App\Http\Controllers\Traits\HasStudentPartRecords;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Console\Command;

class BackfillMafsilMastery extends Command
{
    use HasStudentPartRecords;

    protected $signature = 'student-parts:backfill-mafsil-mastery
                            {--student= : Process a specific student ID only}
                            {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Recalculate StudentPartRecord rows so that any part with a passed mafsilsession becomes مفصل, even if it was previously overridden manually.';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $specificStudent = $this->option('student');

        $studentQuery = Student::query();
        if ($specificStudent) {
            $studentQuery->where('id', $specificStudent);
        } else {
            $studentIds = RecitationSession::query()
                ->where('type', RecitationSession::TYPE_MAFSIL)
                ->where('status', RecitationSession::STATUS_PASSED)
                ->pluck('student_id')
                ->unique();

            $studentQuery->whereIn('id', $studentIds);
        }

        $students = $studentQuery->with('user')->get();
        if ($students->isEmpty()) {
            $this->warn('No students with mafsilsessions found.');
            return 0;
        }

        $this->info(sprintf(
            '%s %d student(s) with mafsilsessions...',
            $dryRun ? 'Previewing' : 'Processing',
            $students->count()
        ));

        $totals = ['updated' => 0, 'cleared_overrides' => 0, 'mafsil_parts' => 0];

        foreach ($students as $student) {
            $allSessions = $this->getSessionsForStudent($student->id);
            if ($allSessions->isEmpty()) {
                continue;
            }

            $existing = StudentPartRecord::where('student_id', $student->id)
                ->get()
                ->keyBy('part_number');

            $partSessionTypes = $this->buildPartSessionTypes($allSessions);
            $mafsilParts = array_keys(array_filter(
                $partSessionTypes,
                fn($type) => $type === RecitationSession::TYPE_MAFSIL
            ));

            if (empty($mafsilParts)) {
                continue;
            }

            $totals['mafsil_parts'] += count($mafsilParts);

            $updatesForStudent = [];
            $clearedOverrides = 0;
            foreach ($mafsilParts as $partNumber) {
                $record = $existing->get($partNumber);
                $wasOverride = $record && $record->is_manual_override;
                if ($wasOverride) {
                    $clearedOverrides++;
                }

                $sessionsByPart = $allSessions->groupBy(fn($s) => $s->part?->part_number);
                $calculated = $this->calculatePartRecordWithGrouped(
                    $sessionsByPart,
                    (int) $partNumber,
                    $partSessionTypes
                );

                if ($record && ! $this->recordIsDirty($record, $calculated)) {
                    continue;
                }

                $updatesForStudent[(int) $partNumber] = [
                    'part_number' => (int) $partNumber,
                    'from' => $record ? [
                        'mastery_status' => $record->mastery_status,
                        'is_manual_override' => (bool) $record->is_manual_override,
                    ] : null,
                    'to' => $calculated,
                    'cleared_override' => $wasOverride,
                ];
            }

            if (empty($updatesForStudent)) {
                continue;
            }

            $this->line(sprintf(
                '  - %s (id=%d): %d part(s) affected, %d manual override(s) cleared',
                $student->user->name ?? 'بدون اسم',
                $student->id,
                count($updatesForStudent),
                $clearedOverrides
            ));

            foreach ($updatesForStudent as $partNumber => $change) {
                $this->line(sprintf(
                    '      • part %d: %s -> %s%s',
                    $partNumber,
                    $change['from']['mastery_status'] ?? 'null',
                    $change['to']['mastery_status'],
                    $change['cleared_override'] ? ' (cleared manual override)' : ''
                ));
            }

            $totals['updated'] += count($updatesForStudent);
            $totals['cleared_overrides'] += $clearedOverrides;

            if ($dryRun) {
                continue;
            }

            \DB::transaction(function () use ($student, $updatesForStudent) {
                foreach ($updatesForStudent as $partNumber => $change) {
                    StudentPartRecord::updateOrCreate(
                        ['student_id' => $student->id, 'part_number' => $partNumber],
                        $change['to']
                    );
                }
            });
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. %s%d record(s) updated, %d manual override(s) cleared across %d mafsiltested part(s).',
            $dryRun ? '[DRY RUN] Would update ' : '',
            $totals['updated'],
            $totals['cleared_overrides'],
            $totals['mafsil_parts']
        ));

        return 0;
    }
}