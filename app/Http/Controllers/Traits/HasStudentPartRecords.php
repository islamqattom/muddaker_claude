<?php

namespace App\Http\Controllers\Traits;

use App\Models\QuranPage;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Database\Eloquent\Collection;

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

        $records = $existingRecords->union($partsToFill)->filter();
        return $records;
    }

    protected function buildPartSessionTypes($allSessions): array
    {
        $partSessionTypes = [];

        foreach ($allSessions as $session) {
            if ($session->type === RecitationSession::TYPE_MAFSIL) {
                $partNumbers = $session->pages->pluck('page_number')->toArray();
                foreach ($partNumbers as $partNumber) {
                    $partSessionTypes[$partNumber] = RecitationSession::TYPE_MAFSIL;
                }
            } elseif ($session->type === RecitationSession::TYPE_RECITATION) {
                $partNum = $session->part?->part_number;
                if ($partNum && !isset($partSessionTypes[$partNum])) {
                    $partSessionTypes[$partNum] = $session->type;
                }
            }
        }

        return $partSessionTypes;
    }

    protected function calculatePartRecordWithGrouped($sessionsByPart, int $partNumber): array
    {
        $partSessions = $sessionsByPart->get($partNumber, collect());

        if ($partSessions->isEmpty()) {
            return [
                'is_completed' => false,
                'total_errors' => 0,
                'total_hesitations' => 0,
                'total_score' => null,
                'last_recitation_date' => null,
                'is_manual_override' => false,
            ];
        }

        return [
            'is_completed' => true,
            'total_errors' => $partSessions->sum('total_errors'),
            'total_hesitations' => $partSessions->sum('total_hesitations'),
            'total_score' => $partSessions->avg('final_score'),
            'last_recitation_date' => $partSessions->max('session_date'),
            'is_manual_override' => false,
        ];
    }

    private function recordIsDirty($existing, $calculated): bool
    {
        return $existing->total_errors != $calculated['total_errors']
            || $existing->total_hesitations != $calculated['total_hesitations']
            || $existing->total_score != $calculated['total_score']
            || $existing->is_completed != $calculated['is_completed'];
    }
}