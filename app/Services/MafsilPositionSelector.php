<?php

namespace App\Services;

use App\Models\MafsilPosition;
use App\Models\MafsilPositionUsage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Selects Mafsil positions per part/juz with the following rules:
 *
 *  - Per-part: 3 positions, one per difficulty (default). Configurable via
 *    $distribution[part][d] ∈ {1,2,3}.
 *  - No two positions in the same part may be "adjacent" (see isAdjacent()).
 *    Adjacency is checked across ALL difficulties in the part, not just
 *    within one difficulty.
 *  - Within each part, candidates with the smallest pool size are processed
 *    first so they aren't starved.
 *  - For each (part, difficulty), phases in order:
 *      1. UNUSED + non-adjacent
 *      2. (only if cycle truly exhausted: total_unused == 0)
 *         mark the cycle as completed_at=now() and pick from the full pool
 *         (non-adjacent, excluding picks-so-far).
 *      2b. (only when cycle NOT closed but unused positions remain and all
 *          are adjacent to picks-so-far): pick from USED non-adjacent
 *          positions without recording.
 *      3. Adjacent positions (last resort, not recorded).
 *      4. Placeholders (no DB row, no ayah FK, negative ids).
 *
 *  - Tie-breaking on global usage count is done by SHUFFLING FIRST and then
 *    STABLE-sorting by ascending count. Sorting first destroys randomness.
 *
 *  - Recording: only Phase 1 and Phase 2 picks become rows in
 *    mafsil_position_usages. Phase 1 picks get completed_at = now() when the
 *    cycle was closed in this round, otherwise completed_at = null.
 *    Phase 2 picks always get completed_at = null (new active cycle).
 *
 *  - selectAndRecord() wraps the entire selection+recording in
 *    DB::transaction so a failure cannot leave a closed cycle without fresh
 *    rows.
 */
class MafsilPositionSelector
{
    public const MIN_GAP = 2;

    public const MIN_AYAH_GAP = 1;

    /**
     * Pure selection — no DB writes. For the guest flow and for unit tests.
     */
    public function select(array $parts, array $distribution, ?int $studentId): Collection
    {
        return $this->runSelection($parts, $distribution, $studentId, null, recordUsage: false);
    }

    /**
     * Selection + usage recording in a single transaction.
     */
    public function selectAndRecord(
        array $parts,
        array $distribution,
        ?int $studentId,
        ?int $supervisorId
    ): Collection {
        return DB::transaction(function () use ($parts, $distribution, $studentId, $supervisorId) {
            return $this->runSelection($parts, $distribution, $studentId, $supervisorId, recordUsage: true);
        });
    }

    /**
     * @param array<int>           $parts        juz_numbers to pick positions for.
     * @param array<int,array<int,int>> $distribution  [$partNumber => [1=>n, 2=>n, 3=>n]]
     * @param int|null             $studentId    null for guest flow.
     * @param int|null             $supervisorId null for guest flow.
     * @param bool                  $recordUsage  when true, INSERT into usages at the end.
     * @return Collection<int,Collection<int,MafsilPosition>> keyed by juz_number.
     */
    private function runSelection(
        array $parts,
        array $distribution,
        ?int $studentId,
        ?int $supervisorId,
        bool $recordUsage
    ): Collection {
        $allPositions = MafsilPosition::with(['startAyah.surah', 'endAyah.surah'])
            ->whereIn('juz_number', $parts)
            ->get()
            ->groupBy(['juz_number', 'difficulty']);

        $poolIds = $allPositions->flatten(1)->pluck('id')->all();
        $globalCounts = $recordUsage
            ? $this->globalUsageCounts($poolIds)
            : $this->zeroGlobalUsageCounts($poolIds);

        $pendingRows = [];
        $byPart = collect();

        foreach ($parts as $partNumber) {
            $partSelected = $this->pickForPart(
                $partNumber,
                $distribution[$partNumber] ?? [],
                $allPositions,
                $studentId,
                $supervisorId,
                $globalCounts,
                $pendingRows,
                $recordUsage
            );

            $byPart->put(
                $partNumber,
                $this->orderForDisplay($partSelected)
            );
        }

        if ($recordUsage && !empty($pendingRows)) {
            MafsilPositionUsage::insert($pendingRows);
        }

        // Display/stash order: parts rendered ascending by juz number. Selection
        // itself is unaffected (it runs before this in the loop order above).
        return $byPart->sortKeys();
    }

    /**
     * Display order within a part: ascending by the start ayah's mushaf page
     * number, then by start ayah id. Placeholders (no start ayah) sort last,
     * ordered among themselves by list_order.
     */
    private function orderForDisplay(Collection $positions): Collection
    {
        // The leading shuffle() is kept purely to preserve the exact RNG stream:
        // the previous implementation consumed one shuffle() per part here, and
        // that draw feeds the shared mt_rand stream used by selection of later
        // parts. Keeping it guarantees selection is unchanged for a fixed seed.
        // Its output is fully overridden by the deterministic sort below.
        return $positions->shuffle()->sort(function ($a, $b) {
            $pa = $a->startAyah->page_number ?? PHP_INT_MAX;
            $pb = $b->startAyah->page_number ?? PHP_INT_MAX;

            if ((int) $pa !== (int) $pb) {
                return ((int) $pa) <=> ((int) $pb);
            }
            if ($pa === PHP_INT_MAX) {
                return ((int) $a->list_order) <=> ((int) $b->list_order);
            }

            return ((int) $a->start_ayah_id) <=> ((int) $b->start_ayah_id);
        })->values();
    }

    /**
     * Process a part: ordered difficulty loop, adjacency-filtered phases.
     *
     * @param Collection<int,Collection<int,MafsilPosition>> $allPositions
     * @param array<int,int> $distForPart [1=>n, 2=>n, 3=>n]
     * @param array<int,int> $globalCounts
     * @param array<int,array<string,mixed>> $pendingRows by-ref collector
     */
    private function pickForPart(
        int $partNumber,
        array $distForPart,
        Collection $allPositions,
        ?int $studentId,
        ?int $supervisorId,
        array $globalCounts,
        array &$pendingRows,
        bool $recordUsage
    ): Collection {
        $picked = collect();
        $pool = $allPositions->get($partNumber, collect());

        $diffs = [1, 2, 3];
        usort($diffs, function (int $a, int $b) use ($pool, $distForPart) {
            $na = $distForPart[$a] ?? 0;
            $nb = $distForPart[$b] ?? 0;
            $ca = $na > 0 ? $pool->get($a, collect())->count() : PHP_INT_MAX;
            $cb = $nb > 0 ? $pool->get($b, collect())->count() : PHP_INT_MAX;
            return $ca <=> $cb;
        });

        foreach ($diffs as $d) {
            $need = (int) ($distForPart[$d] ?? 0);
            if ($need <= 0) {
                continue;
            }

            $cand = $pool->get($d, collect());
            if ($cand->isEmpty()) {
                // Phase 4 only — pool empty.
                $picked = $picked->merge($this->buildPlaceholders($partNumber, $need, $d, $picked->count()));
                continue;
            }

            $activeIds = $recordUsage && $studentId !== null
                ? $this->activeUsageIdsFor($studentId, $partNumber, $d)
                : [];

$unused = $cand->reject(fn ($p) => in_array($p->id, $activeIds, true));
            $used = $cand->filter(fn ($p) => in_array($p->id, $activeIds, true));
            $initialUnusedCount = $unused->count();

            // Phase 1: unused + non-adjacent. Greedy one-at-a-time so adjacency among the
            // positions chosen in this step is enforced, with retries on fresh shuffles.
            $chosen = $this->greedyNonAdjacent($unused, $picked, $need, $globalCounts);
            $picked = $picked->merge($chosen);
            $need -= $chosen->count();

            // After Phase 1, how many unused positions remain un-picked?
            $remainingUnused = $initialUnusedCount - $chosen->count();

            $cycleClosed = false;
            if ($need > 0 && $remainingUnused === 0) {
                // No unused positions left at all (regardless of adjacency) → close cycle
                // and continue from the grabber pool in Phase 2.
                if ($recordUsage && $studentId !== null) {
                    $this->closeActiveCycle($studentId, $partNumber, $d);
                }
                $cycleClosed = true;
            }

            // Phase 2: only if cycle was closed.
            $fresh = collect();
            if ($cycleClosed && $need > 0) {
                $phase2Candidates = $cand->reject(fn ($p) => $picked->contains(fn ($q) => $q->id === $p->id));
                $fresh = $this->greedyNonAdjacent($phase2Candidates, $picked, $need, $globalCounts);
                $picked = $picked->merge($fresh);
                $need -= $fresh->count();
            }

// Phase 2b: cycle NOT closed but unused positions remain (all are adjacent to picks).
// Fill from USED non-adjacent positions without recording.
            if (!$cycleClosed && $need > 0 && $remainingUnused > 0) {
                $usedCandidates = $used->reject(fn ($p) => $picked->contains(fn ($q) => $q->id === $p->id));
                $usedFills = $this->greedyNonAdjacent($usedCandidates, $picked, $need, $globalCounts);
                $picked = $picked->merge($usedFills);
                $need -= $usedFills->count();
                // not recorded
            }

            // Phase 3: adjacent (last resort before placeholders).
            if ($need > 0) {
                $adjacent = $cand->reject(fn ($p) => $picked->contains(fn ($q) => $q->id === $p->id))->shuffle();
                $adjacentPicks = $adjacent->take($need)->values();
                $picked = $picked->merge($adjacentPicks);
                $need -= $adjacentPicks->count();
                // not recorded
            }

            // Phase 4: placeholders.
            if ($need > 0) {
                $picked = $picked->merge($this->buildPlaceholders($partNumber, $need, $d, $picked->count()));
            }

            if ($recordUsage) {
                $now = now();
                foreach ($chosen as $p) {
                    if ($this->isReal($p)) {
                        $pendingRows[] = [
                            'juz_number' => $partNumber,
                            'difficulty' => $d,
                            'mafsil_position_id' => $p->id,
                            'supervisor_id' => $supervisorId,
                            'student_id' => $studentId,
                            'completed_at' => $cycleClosed ? $now : null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
                foreach ($fresh as $p) {
                    if ($this->isReal($p)) {
                        $pendingRows[] = [
                            'juz_number' => $partNumber,
                            'difficulty' => $d,
                            'mafsil_position_id' => $p->id,
                            'supervisor_id' => $supervisorId,
                            'student_id' => $studentId,
                            'completed_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        return $picked;
    }

    private function isAdjacent(MafsilPosition $a, MafsilPosition $b): bool
    {
        if ($a->list_order > 0 && $b->list_order > 0
            && abs((int) $a->list_order - (int) $b->list_order) < self::MIN_GAP) {
            return true;
        }

        $gap = $this->ayahGap($a, $b);

        return $gap !== null && $gap <= self::MIN_AYAH_GAP;
    }

    /**
     * Signed gap between two ayah ranges. Order the two positions by start ayah;
     * gap = later.start_ayah_id - earlier.end_ayah_id. Returns null when either
     * range lacks ayah FKs (e.g. placeholders).
     *
     *   gap = 0  → shared boundary ayah
     *   gap = 1  → touching ranges (consecutive)
     *   gap < 0  → overlapping ranges
     */
    private function ayahGap(MafsilPosition $a, MafsilPosition $b): ?int
    {
        $aS = (int) $a->start_ayah_id;
        $aE = (int) $a->end_ayah_id;
        $bS = (int) $b->start_ayah_id;
        $bE = (int) $b->end_ayah_id;

        if ($aS === 0 || $aE === 0 || $bS === 0 || $bE === 0) {
            return null;
        }

        return $aS <= $bS ? $bS - $aE : $aS - $bE;
    }

    /**
     * Pick up to $need candidates that are non-adjacent to each other and to
     * everything already in $picked. The order is shuffled then stable-sorted by
     * ascending global usage count (random tie-break). A single greedy pass can
     * get blocked by chance (e.g. picking a middle position that blocks both
     * neighbours), so retry with fresh shuffles and keep the best result.
     */
    private function greedyNonAdjacent(
        Collection $candidates,
        Collection $picked,
        int $need,
        array $globalCounts
    ): Collection {
        $best = collect();

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $attemptPicks = $this->greedyAttempt($candidates, $picked, $need, $globalCounts);

            if ($attemptPicks->count() > $best->count()) {
                $best = $attemptPicks;
            }
            if ($best->count() >= $need) {
                break;
            }
        }

        return $best;
    }

    private function greedyAttempt(
        Collection $candidates,
        Collection $picked,
        int $need,
        array $globalCounts
    ): Collection {
        $ordered = $this->shuffleThenStableSortByGlobalCount($candidates, $globalCounts);

        $acc = $picked->values();   // working copy; never mutate the caller's collection
        $chosen = collect();

        foreach ($ordered as $cand) {
            if ($chosen->count() >= $need) {
                break;
            }
            if ($acc->contains(fn ($p) => $p->id === $cand->id)) {
                continue;
            }

            $adjacent = false;
            foreach ($acc as $p) {
                if ($this->isAdjacent($p, $cand)) {
                    $adjacent = true;
                    break;
                }
            }

            if (! $adjacent) {
                $chosen->push($cand);
                $acc->push($cand);
            }
        }

        return $chosen;
    }

    private function shuffleThenStableSortByGlobalCount(Collection $items, array $globalCounts): Collection
    {
        // shuffle first to randomize ties, then stable-sort by count ascending.
        return $items->shuffle()->sortBy(
            fn ($p) => $globalCounts[$p->id] ?? 0,
            SORT_REGULAR,
            true // stable
        )->values();
    }

    private function globalUsageCounts(array $positionIds): array
    {
        if (empty($positionIds)) {
            return [];
        }
        return DB::table('mafsil_position_usages')
            ->whereIn('mafsil_position_id', $positionIds)
            ->groupBy('mafsil_position_id')
            ->selectRaw('mafsil_position_id, COUNT(*) as c')
            ->pluck('c', 'mafsil_position_id')
            ->all();
    }

    private function zeroGlobalUsageCounts(array $positionIds): array
    {
        return array_fill_keys($positionIds, 0);
    }

    private function activeUsageIdsFor(int $studentId, int $partNumber, int $difficulty): array
    {
        return MafsilPositionUsage::active()
            ->where('student_id', $studentId)
            ->where('juz_number', $partNumber)
            ->where('difficulty', $difficulty)
            ->pluck('mafsil_position_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function closeActiveCycle(int $studentId, int $partNumber, int $difficulty): void
    {
        MafsilPositionUsage::where('student_id', $studentId)
            ->where('juz_number', $partNumber)
            ->where('difficulty', $difficulty)
            ->whereNull('completed_at')
            ->update(['completed_at' => now()]);
    }

    private function isReal(MafsilPosition $p): bool
    {
        return $p->id > 0 && $p->start_ayah_id !== null;
    }

    private function buildPlaceholders(int $partNumber, int $count, int $difficulty, int $startOrder): Collection
    {
        $placeholders = collect();
        for ($i = 0; $i < $count; $i++) {
            $p = new MafsilPosition();
            $p->id = -1 * (($partNumber * 100) + $startOrder + $i + 1);
            $p->juz_number = $partNumber;
            $p->position_number = $startOrder + $i + 1;
            $p->list_order = $startOrder + $i + 1;
            $p->start_ayah_id = null;
            $p->end_ayah_id = null;
            $p->difficulty = $difficulty > 0 ? $difficulty : 2;
            $p->notes = null;
            $p->exists = false;
            $placeholders->push($p);
        }
        return $placeholders;
    }
}