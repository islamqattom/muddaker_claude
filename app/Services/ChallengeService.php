<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Challenge;
use App\Models\ChallengeVerification;
use App\Models\RecitationSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Challenge Service
 *
 * Manages peer-to-peer study challenges between students.
 */
class ChallengeService
{
    /**
     * Challenge statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Create a new challenge
     *
     * @param Student $challenger
     * @param Student $challenged
     * @param int $surah
     * @param int $verseStart
     * @param int $verseEnd
     * @param Carbon $targetDate
     * @return Challenge
     */
    public function createChallenge(
        Student $challenger,
        Student $challenged,
        int $surah,
        int $verseStart,
        int $verseEnd,
        Carbon $targetDate
    ): Challenge {
        // Validate verse range
        if ($verseStart > $verseEnd) {
            throw new \InvalidArgumentException('verse_start cannot be greater than verse_end');
        }

        // Prevent self-challenge
        if ($challenger->id === $challenged->id) {
            throw new \InvalidArgumentException('Cannot challenge yourself');
        }

        // Prevent duplicate active challenges
        $existing = Challenge::where(function ($q) use ($challenger, $challenged) {
            $q->where('challenger_id', $challenger->id)
              ->where('challenged_id', $challenged->id);
        })
        ->whereIn('status', [self::STATUS_PENDING, self::STATUS_ACCEPTED])
        ->exists();

        if ($existing) {
            throw new \InvalidArgumentException('Active challenge already exists with this opponent');
        }

        return Challenge::create([
            'challenger_id' => $challenger->id,
            'challenged_id' => $challenged->id,
            'surah' => $surah,
            'verse_start' => $verseStart,
            'verse_end' => $verseEnd,
            'target_date' => $targetDate,
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Accept a challenge
     *
     * @param Challenge $challenge
     * @return Challenge
     */
    public function accept(Challenge $challenge): Challenge
    {
        if ($challenge->status !== self::STATUS_PENDING) {
            throw new \LogicException('Can only accept pending challenges');
        }

        $challenge->status = self::STATUS_ACCEPTED;
        $challenge->save();
        return $challenge;
    }

    /**
     * Decline a challenge
     *
     * @param Challenge $challenge
     * @return Challenge
     */
    public function decline(Challenge $challenge): Challenge
    {
        if ($challenge->status !== self::STATUS_PENDING) {
            throw new \LogicException('Can only decline pending challenges');
        }

        $challenge->status = self::STATUS_DECLINED;
        $challenge->save();
        return $challenge;
    }

    /**
     * Verify completion of a challenge for a student
     *
     * @param Challenge $challenge
     * @param Student $student
     * @param RecitationSession $session
     * @return ChallengeVerification
     */
    public function verifyCompletion(Challenge $challenge, Student $student, RecitationSession $session): ChallengeVerification
    {
        return ChallengeVerification::updateOrCreate(
            [
                'challenge_id' => $challenge->id,
                'student_id' => $student->id,
            ],
            [
                'recitation_session_id' => $session->id,
                'verified_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Check and update challenge status based on completions
     *
     * @param Challenge $challenge
     * @return Challenge
     */
    public function checkChallengeStatus(Challenge $challenge): Challenge
    {
        // Only process accepted challenges
        if ($challenge->status !== self::STATUS_ACCEPTED) {
            return $challenge;
        }

        return DB::transaction(function () use ($challenge) {
            // Lock the challenge row for update to prevent race conditions
            $challenge = Challenge::lockForUpdate()->find($challenge->id);

            // Get both students' verifications in a single query
            $verifications = $challenge->verifications()
                ->whereIn('student_id', [$challenge->challenger_id, $challenge->challenged_id])
                ->with('recitationSession')
                ->get();

            $challengerVerification = $verifications->firstWhere('student_id', $challenge->challenger_id);
            $challengedVerification = $verifications->firstWhere('student_id', $challenge->challenged_id);

            // Both completed
            if ($challengerVerification && $challengedVerification) {
                $challenge->status = self::STATUS_COMPLETED;

                // Determine winner based on completion time
                if ($challengerVerification->verified_at->lt($challengedVerification->verified_at)) {
                    $challenge->winner_id = $challenge->challenger_id;
                } elseif ($challengedVerification->verified_at->lt($challengerVerification->verified_at)) {
                    $challenge->winner_id = $challenge->challenged_id;
                } else {
                    // Tie - use accuracy (only if both have valid sessions)
                    $challengerSession = $challengerVerification->recitationSession;
                    $challengedSession = $challengedVerification->recitationSession;

                    if (!$challengerSession || !$challengedSession) {
                        Log::error('ChallengeService: Missing recitation session for challenge verification', [
                            'challenge_id' => $challenge->id,
                            'challenger_has_session' => !$challengerSession,
                            'challenged_has_session' => !$challengedSession,
                        ]);
                        $challenge->status = self::STATUS_FAILED;
                        $challenge->winner_id = null;
                    } else {
                        // Use > (not >=) to give tie-breaker to challenged on equal scores
                        $challenge->winner_id = $challengerSession->final_score > $challengedSession->final_score
                            ? $challenge->challenger_id
                            : $challenge->challenged_id;
                    }
                }
            }
            // Check if deadline passed with only one completion
            elseif ($challenge->target_date->lt(Carbon::today())) {
                if ($challengerVerification && !$challengedVerification) {
                    $challenge->status = self::STATUS_COMPLETED;
                    $challenge->winner_id = $challenge->challenger_id;
                } elseif ($challengedVerification && !$challengerVerification) {
                    $challenge->status = self::STATUS_COMPLETED;
                    $challenge->winner_id = $challenge->challenged_id;
                } else {
                    $challenge->status = self::STATUS_FAILED;
                }
            }

            $challenge->save();
            return $challenge;
        });
    }

    /**
     * Get active challenges for a student
     *
     * @param Student $student
     * @return Collection
     */
    public function getActiveChallenges(Student $student): Collection
    {
        return Challenge::where(function ($query) use ($student) {
            $query->where('challenger_id', $student->id)
                ->orWhere('challenged_id', $student->id);
        })
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_ACCEPTED])
            ->with(['challenger.user', 'challenged.user', 'verifications'])
            ->orderBy('target_date')
            ->get();
    }

    /**
     * Get pending challenge requests for a student
     *
     * @param Student $student
     * @return Collection
     */
    public function getPendingRequests(Student $student): Collection
    {
        return Challenge::where('challenged_id', $student->id)
            ->where('status', self::STATUS_PENDING)
            ->with('challenger.user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get challenge history for a student
     *
     * @param Student $student
     * @param int $limit
     * @return Collection
     */
    public function getHistory(Student $student, int $limit = 20): Collection
    {
        return Challenge::where(function ($query) use ($student) {
            $query->where('challenger_id', $student->id)
                ->orWhere('challenged_id', $student->id);
        })
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_DECLINED])
            ->with(['challenger.user', 'challenged.user', 'verifications'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get challenge stats for a student
     *
     * @param Student $student
     * @return array{won: int, lost: int, total: int, pending: int}
     */
    public function getStats(Student $student): array
    {
        $baseQuery = Challenge::where(function ($query) use ($student) {
            $query->where('challenger_id', $student->id)
                ->orWhere('challenged_id', $student->id);
        });

        // Use conditional aggregation for completed/failed stats
        $stats = (clone $baseQuery)
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_FAILED])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN winner_id = ? THEN 1 ELSE 0 END) as won
            ", [$student->id])
            ->first();

        $pending = (clone $baseQuery)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_ACCEPTED])
            ->count();

        return [
            'won' => (int) ($stats->won ?? 0),
            'lost' => (int) (($stats->total ?? 0) - ($stats->won ?? 0)),
            'total' => (int) ($stats->total ?? 0),
            'pending' => $pending,
        ];
    }

    /**
     * Get available students to challenge (excluding self and recent opponents)
     *
     * @param Student $student
     * @param int $limit
     * @return Collection
     */
    public function getAvailableOpponents(Student $student, int $limit = 10): Collection
    {
        // Get recent opponent IDs without loading full models
        $recentOpponentIds = Challenge::where(function ($query) use ($student) {
            $query->where('challenger_id', $student->id)
                ->orWhere('challenged_id', $student->id);
        })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select('challenger_id', 'challenged_id')
            ->get()
            ->flatMap(fn($c) => [$c->challenger_id, $c->challenged_id])
            ->filter(fn($id) => $id !== $student->id)
            ->unique()
            ->toArray();

        return Student::active()->with('user', 'level')
            ->where('id', '!=', $student->id)
            ->whereNotIn('id', $recentOpponentIds)
            ->orderBy('current_streak', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get status label in Arabic
     *
     * @param string $status
     * @return string
     */
    public static function getStatusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'في انتظار القبول',
            self::STATUS_ACCEPTED => 'مقبول - قيد التنفيذ',
            self::STATUS_DECLINED => 'مرفوض',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_FAILED => 'فشل',
            default => $status,
        };
    }
}
