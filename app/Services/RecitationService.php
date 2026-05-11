<?php

namespace App\Services;

use App\Models\RecitationSession;

class RecitationService
{
    public function ensureNotRecitateSessions(array $studentIds, int $supervisorId, string $date): void
    {
        if (empty($studentIds)) {
            return;
        }

        $existingStudentIds = RecitationSession::where('session_date', $date)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id');

        $missingStudentIds = array_diff($studentIds, $existingStudentIds->toArray());

        if (empty($missingStudentIds)) {
            return;
        }

        $now = now();
        $records = array_map(fn($studentId) => [
            'student_id' => $studentId,
            'supervisor_id' => $supervisorId,
            'type' => RecitationSession::TYPE_NOT_RECITE,
            'status' => RecitationSession::STATUS_NOT_RECITE,
            'session_date' => $date,
            'notes' => 'تسميع تلقائي - لم يتم تسجيل تسميع',
            'created_at' => $now,
            'updated_at' => $now,
        ], $missingStudentIds);

        RecitationSession::insert($records);
    }
}