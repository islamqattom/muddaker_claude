<?php

namespace App\Http\Controllers\Traits;

use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

trait HasStudentPartRecords
{
    protected function getSessionsForStudent(int $studentId): Collection
    {
        return RecitationSession::where('student_id', $studentId)
            ->whereIn('type', [RecitationSession::TYPE_RECITATION, RecitationSession::TYPE_MAFSIL])
            ->where('status', RecitationSession::STATUS_PASSED)
            ->with(['part', 'pages'])
            ->get();
    }

    protected function autoFillRecords(Student $student, Collection $allSessions): \Illuminate\Support\Collection
    {
        $existingRecords = StudentPartRecord::where('student_id', $student->id)->get()->keyBy('part_number');
        $sessionsByPart = $allSessions->groupBy(fn($s) => $s->part?->part_number);

        $partsToFill = [];
        foreach (range(1, 30) as $partNumber) {
            $existing = $existingRecords->get($partNumber);

            if ($existing && $existing->is_manual_override) {
                continue;
            }

            $calculated = $this->calculatePartRecordWithGrouped($sessionsByPart, $partNumber);

            if (!$existing || $this->recordIsDirty($existing, $calculated)) {
                $partsToFill[$partNumber] = $calculated;
            }
        }

        foreach ($partsToFill as $partNumber => $data) {
            StudentPartRecord::updateOrCreate(
                ['student_id' => $student->id, 'part_number' => $partNumber],
                $data
            );
        }

        // Convert partsToFill arrays to StudentPartRecord model instances before merging
        $newRecords = collect($partsToFill)->map(fn($data, $partNum) => new StudentPartRecord(
            array_merge(['student_id' => $student->id, 'part_number' => $partNum], $data)
        ));

        $records = $existingRecords->merge($newRecords)->keyBy('part_number');
        return $records;
    }

    protected function buildPartSessionTypes($allSessions): array
    {
        $partSessionTypes = [];

        // Process mafsil first so recitation (passed sard) always takes precedence
        foreach ($allSessions as $session) {
            if ($session->type === RecitationSession::TYPE_MAFSIL) {
                $partNumbers = $session->pages->pluck('page_number')->toArray();
                foreach ($partNumbers as $partNumber) {
                    if (!isset($partSessionTypes[$partNumber])) {
                        $partSessionTypes[$partNumber] = RecitationSession::TYPE_MAFSIL;
                    }
                }
            }
        }

        // Recitation always overwrites (passed sard takes priority over mafsil)
        foreach ($allSessions as $session) {
            if ($session->type === RecitationSession::TYPE_RECITATION) {
                $partNum = $session->part?->part_number;
                if ($partNum) {
                    $partSessionTypes[$partNum] = RecitationSession::TYPE_RECITATION;
                }
            }
        }

        return $partSessionTypes;
    }

    protected function calculatePartRecordWithGrouped($sessionsByPart, int $partNumber): array
    {
        $partSessions = $sessionsByPart->get($partNumber, collect());
        $hasPassedSessions = !$partSessions->isEmpty();

        if (!$hasPassedSessions) {
            return [
                'is_completed' => false,
                'total_errors' => 0,
                'total_hesitations' => 0,
                'total_score' => null,
                'last_recitation_date' => null,
                'is_manual_override' => false,
                'mastery_status' => StudentPartRecord::MASTERY_NOT_MASTERED,
            ];
        }

        return [
            'is_completed' => true,
            'total_errors' => $partSessions->sum('total_errors'),
            'total_hesitations' => $partSessions->sum('total_hesitations'),
            'total_score' => $partSessions->avg('final_score'),
            'last_recitation_date' => $partSessions->max('session_date'),
            'is_manual_override' => false,
            'mastery_status' => StudentPartRecord::MASTERY_MASTERED,
        ];
    }

    private function recordIsDirty($existing, $calculated): bool
    {
        return $existing->total_errors != $calculated['total_errors']
            || $existing->total_hesitations != $calculated['total_hesitations']
            || $existing->total_score != $calculated['total_score']
            || $existing->is_completed != $calculated['is_completed']
            || $existing->mastery_status != $calculated['mastery_status'];
    }

    protected function updatePartRecord(int $studentId, int $partNumber, array $validated): bool
    {
        DB::transaction(function () use ($studentId, $partNumber, $validated) {
            StudentPartRecord::updateOrCreate(
                ['student_id' => $studentId, 'part_number' => $partNumber],
                [
                    'is_completed' => true,
                    'total_errors' => $validated['total_errors'] ?? 0,
                    'total_hesitations' => $validated['total_hesitations'] ?? 0,
                    'total_score' => $validated['total_score'] ?? null,
                    'last_recitation_date' => $validated['session_date'] ?? null,
                    'is_manual_override' => true,
                    'mastery_status' => $validated['mastery_status'] ?? StudentPartRecord::MASTERY_NOT_MASTERED,
                ]
            );
        });

        return true;
    }

    protected function resetPartRecordToAuto(int $studentId, int $partNumber): void
    {
        $calculated = $this->calculatePartRecordWithGrouped(
            $this->getSessionsForStudent($studentId)->groupBy(fn($s) => $s->part?->part_number),
            $partNumber
        );

        StudentPartRecord::updateOrCreate(
            ['student_id' => $studentId, 'part_number' => $partNumber],
            array_merge($calculated, ['is_manual_override' => false])
        );
    }

    protected function getDisplayedMasteryStatus(?string $sessionType, $record): string
    {
        $isOverride = $record && $record->is_manual_override;
        $recordMasteryStatus = $record->mastery_status ?? null;

        if ($isOverride && $recordMasteryStatus === StudentPartRecord::MASTERY_MASTERED) {
            return StudentPartRecord::MASTERY_MASTERED;
        }
        if ($isOverride && $recordMasteryStatus === StudentPartRecord::MASTERY_NOT_MASTERED) {
            return StudentPartRecord::MASTERY_NOT_MASTERED;
        }
        if ($isOverride && $recordMasteryStatus === StudentPartRecord::MASTERY_MAFSIL) {
            return StudentPartRecord::MASTERY_MAFSIL;
        }
        if ($sessionType === RecitationSession::TYPE_MAFSIL) {
            return 'مفصل';
        }
        if ($sessionType === RecitationSession::TYPE_RECITATION) {
            return $record && $record->is_completed
                ? StudentPartRecord::MASTERY_MASTERED
                : StudentPartRecord::MASTERY_NOT_MASTERED;
        }
        return StudentPartRecord::MASTERY_NOT_MASTERED;
    }

    protected function countMafsilAndMutqin(\Illuminate\Support\Collection $records, array $partSessionTypes): int
    {
        $count = 0;
        foreach ($records as $partNumber => $record) {
            $sessionType = $partSessionTypes[$partNumber] ?? null;
            $status = $this->getDisplayedMasteryStatus($sessionType, $record);
            if ($status === 'مفصل' || $status === StudentPartRecord::MASTERY_MASTERED) {
                $count++;
            }
        }
        return $count;
    }
}