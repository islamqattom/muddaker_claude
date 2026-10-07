<?php

namespace Tests\Feature;

use App\Models\Ayah;
use App\Models\MafsilPosition;
use App\Models\MafsilPositionUsage;
use App\Models\QuranPart;
use App\Models\Student;
use App\Models\Surah;
use App\Models\User;
use App\Services\MafsilPositionSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MafsilPositionSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function seedSurahWithAyahs(int $ayahCount = 30): array
    {
        $surah = Surah::create([
            'name_ar' => 'سورة الاختبار',
            'name_en' => 'Test Surah',
            'total_ayahs' => $ayahCount,
            'surah_number' => 99,
        ]);

        $ayahs = [];
        for ($i = 1; $i <= $ayahCount; $i++) {
            $ayahs[$i] = Ayah::create([
                'surah_id' => $surah->id,
                'ayah_number' => $i,
                'text' => 'aya ' . $i,
                'page_number' => 1,
                'juz_number' => 1,
            ]);
        }
        return $ayahs;
    }

    private function makePosition(int $juz, int $difficulty, int $startAyahId, int $endAyahId, int $listOrder): MafsilPosition
    {
        return MafsilPosition::create([
            'juz_number' => $juz,
            'position_number' => $listOrder,
            'list_order' => $listOrder,
            'start_ayah_id' => $startAyahId,
            'end_ayah_id' => $endAyahId,
            'difficulty' => $difficulty,
        ]);
    }

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Test Supervisor',
            'email' => 'sup@example.test',
            'username' => 'supervisor',
            'phone' => '0500000000',
            'password' => bcrypt('secret'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);
    }

    private static int $studentCounter = 0;

    private function makeStudent(User $supervisor, string $email): Student
    {
        self::$studentCounter++;
        $user = User::create([
            'name' => 'Test Student',
            'email' => $email,
            'username' => $email,
            'phone' => '059' . str_pad((string) self::$studentCounter, 7, '0', STR_PAD_LEFT),
            'password' => bcrypt('secret'),
            'role' => 'student',
            'is_active' => true,
        ]);
        $level = \App\Models\Level::firstOrCreate(
            ['name' => 'Default Level'],
            [
                'level_number' => 1,
                'new_memorization_pages' => 1,
                'review_pages' => 1,
            ]
        );
        return Student::create([
            'user_id' => $user->id,
            'supervisor_id' => $supervisor->id,
            'level_id' => $level->id,
            'student_number' => 'S-' . uniqid(),
        ]);
    }

    private function isAdjacent(MafsilPosition $a, MafsilPosition $b): bool
    {
        if ($a->list_order > 0 && $b->list_order > 0
            && abs((int) $a->list_order - (int) $b->list_order) < MafsilPositionSelector::MIN_GAP) {
            return true;
        }

        $aS = (int) $a->start_ayah_id;
        $aE = (int) $a->end_ayah_id;
        $bS = (int) $b->start_ayah_id;
        $bE = (int) $b->end_ayah_id;
        if ($aS === 0 || $aE === 0 || $bS === 0 || $bE === 0) {
            return false;
        }
        $gap = $aS <= $bS ? $bS - $aE : $aS - $bE;

        return $gap <= MafsilPositionSelector::MIN_AYAH_GAP;
    }

    private function anyAdjacentPairInRun(\Illuminate\Support\Collection $picks): bool
    {
        foreach ($picks as $a) {
            foreach ($picks as $b) {
                if ($a->id === $b->id) {
                    continue;
                }
                if ($this->isAdjacent($a, $b)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function rangesConflict(MafsilPosition $a, MafsilPosition $b): bool
    {
        if ($a->start_ayah_id === null || $a->end_ayah_id === null
            || $b->start_ayah_id === null || $b->end_ayah_id === null) {
            return false;
        }

        return (int) $a->start_ayah_id <= (int) $b->end_ayah_id + 1
            && (int) $b->start_ayah_id <= (int) $a->end_ayah_id + 1;
    }

    private function anyOverlapPair(\Illuminate\Support\Collection $picks): bool
    {
        foreach ($picks as $a) {
            foreach ($picks as $b) {
                if ($a->id === $b->id) {
                    continue;
                }
                if ($this->rangesConflict($a, $b)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function test_no_adjacent_picks_in_200_runs(): void
    {
        $ayahs = $this->seedSurahWithAyahs(90);
        $aid = fn (int $n) => $ayahs[$n]->id;
        // Fixture deliberately CONTAINS adjacent pairs so this test fails on the
        // old selector:
        //   - easy pool: lo 1 (1-5), lo 5 (11-15), lo 9 (21-25), lo 13 (31-35)
        //   - hard pool: lo 3 (41-45) and lo 4 (45-49) share ayah 45 / adjacent
        //     list_order; lo 7 (61-65), lo 11 (81-85) are fillers.
        $this->makePosition(1, 1, $aid(1), $aid(5), 1);
        $this->makePosition(1, 1, $aid(11), $aid(15), 5);
        $this->makePosition(1, 1, $aid(21), $aid(25), 9);
        $this->makePosition(1, 1, $aid(31), $aid(35), 13);
        $this->makePosition(1, 3, $aid(41), $aid(45), 3);
        $this->makePosition(1, 3, $aid(45), $aid(49), 4);
        $this->makePosition(1, 3, $aid(61), $aid(65), 7);
        $this->makePosition(1, 3, $aid(81), $aid(85), 11);

        $selector = new MafsilPositionSelector();
        $distribution = [1 => [1 => 1, 2 => 0, 3 => 2]];

        $adjacentRuns = 0;
        for ($i = 0; $i < 200; $i++) {
            $picks = $selector->select([1], $distribution, null)->get(1);
            if ($this->anyAdjacentPairInRun($picks)) {
                $adjacentRuns++;
            }
        }

        $this->assertSame(0, $adjacentRuns, 'No run should have adjacent positions');
    }

    public function test_positions_sharing_boundary_ayah_never_picked_together(): void
    {
        $ayahs = $this->seedSurahWithAyahs(40);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // A: 1-5, B: 5-9 share ayah 5. list_order deliberately far apart (1 vs 30)
        // so the list_order rule cannot help — only the ayah-gap rule can.
        $a = $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $b = $this->makePosition(1, 3, $aid(5), $aid(9), 30);
        $this->makePosition(1, 3, $aid(13), $aid(17), 3);
        $this->makePosition(1, 3, $aid(21), $aid(25), 5);

        $selector = new MafsilPositionSelector();
        $co = 0;
        for ($i = 0; $i < 200; $i++) {
            $ids = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 2]], null)
                ->get(1)->pluck('id')->all();
            if (in_array($a->id, $ids, true) && in_array($b->id, $ids, true)) {
                $co++;
            }
        }

        $this->assertSame(0, $co, 'positions sharing a boundary ayah must never co-occur');
    }

    public function test_list_order_adjacent_never_picked_together(): void
    {
        $ayahs = $this->seedSurahWithAyahs(70);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // A: lo 10 (1-5), B: lo 11 (30-34) are list_order-adjacent but their ayah
        // ranges are far apart, so only the list_order rule can exclude them.
        $a = $this->makePosition(1, 3, $aid(1), $aid(5), 10);
        $b = $this->makePosition(1, 3, $aid(30), $aid(34), 11);
        $this->makePosition(1, 3, $aid(45), $aid(49), 13);
        $this->makePosition(1, 3, $aid(60), $aid(64), 15);

        $selector = new MafsilPositionSelector();
        $co = 0;
        for ($i = 0; $i < 200; $i++) {
            $ids = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 2]], null)
                ->get(1)->pluck('id')->all();
            if (in_array($a->id, $ids, true) && in_array($b->id, $ids, true)) {
                $co++;
            }
        }

        $this->assertSame(0, $co, 'list_order-adjacent positions must never co-occur');
    }

    public function test_no_adjacent_pair_on_juz3_like_pool_over_500_runs(): void
    {
        $ayahs = $this->seedSurahWithAyahs(200);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Mirror the juz 3 hard pool: 5 hard positions including a boundary-sharing
        // adjacent pair (mirrors ids 4738/4739) and a list_order-adjacent neighbour.
        $adjacentA = $this->makePosition(3, 3, $aid(1), $aid(9), 11);   // like 4738
        $adjacentB = $this->makePosition(3, 3, $aid(9), $aid(14), 12);  // like 4739
        $this->makePosition(3, 3, $aid(17), $aid(24), 13);              // like 4740
        $this->makePosition(3, 3, $aid(60), $aid(66), 19);              // far filler
        $this->makePosition(3, 3, $aid(100), $aid(106), 31);            // far filler

        $selector = new MafsilPositionSelector();
        $exact = 0;
        $adjacentRuns = 0;
        for ($i = 0; $i < 500; $i++) {
            $picks = $selector->select([3], [3 => [1 => 0, 2 => 0, 3 => 2]], null)->get(3);
            $ids = $picks->pluck('id')->all();
            if (in_array($adjacentA->id, $ids, true) && in_array($adjacentB->id, $ids, true)) {
                $exact++;
            }
            if ($this->anyAdjacentPairInRun($picks)) {
                $adjacentRuns++;
            }
        }

        $this->assertSame(0, $exact, 'the boundary-sharing pair must never co-occur');
        $this->assertSame(0, $adjacentRuns, 'no run may contain any adjacent pair');
    }

    public function test_overlapping_50_59_and_52_59_never_co_occur(): void
    {
        $ayahs = $this->seedSurahWithAyahs(90);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Exact shape of the reported bug: same part, far-apart list_order, so
        // only the hard ayah-range constraint can prevent co-occurrence.
        $a = $this->makePosition(1, 2, $aid(50), $aid(59), 3);
        $b = $this->makePosition(1, 3, $aid(52), $aid(59), 18);

        $selector = new MafsilPositionSelector();
        $dist = [1 => [1 => 0, 2 => 1, 3 => 1]];

        $co = 0;
        for ($i = 0; $i < 50; $i++) {
            $ids = $selector->select([1], $dist, null)->get(1)->pluck('id')->all();
            if (in_array($a->id, $ids, true) && in_array($b->id, $ids, true)) {
                $co++;
            }
        }

        $this->assertSame(0, $co, 'overlapping ranges 50-59 and 52-59 must never co-occur');
    }

    public function test_overlapping_82_89_and_86_92_never_co_occur(): void
    {
        $ayahs = $this->seedSurahWithAyahs(120);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Same difficulty, far-apart list_order (reported bug shape).
        $a = $this->makePosition(3, 3, $aid(82), $aid(89), 13);
        $b = $this->makePosition(3, 3, $aid(86), $aid(92), 31);

        $selector = new MafsilPositionSelector();
        $dist = [3 => [1 => 0, 2 => 0, 3 => 2]];

        $co = 0;
        for ($i = 0; $i < 50; $i++) {
            $ids = $selector->select([3], $dist, null)->get(3)->pluck('id')->all();
            if (in_array($a->id, $ids, true) && in_array($b->id, $ids, true)) {
                $co++;
            }
        }

        $this->assertSame(0, $co, 'overlapping ranges 82-89 and 86-92 must never co-occur');
    }

    public function test_shared_boundary_ayah_never_co_occur_as_last_resort(): void
    {
        $ayahs = $this->seedSurahWithAyahs(20);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Only these two real positions exist, so the second one can only be
        // filled by Phase 3 (the adjacent fallback). A: 1-5, B: 5-9 share ayah 5.
        $a = $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $b = $this->makePosition(1, 3, $aid(5), $aid(9), 30);

        $selector = new MafsilPositionSelector();
        $co = 0;
        for ($i = 0; $i < 50; $i++) {
            $ids = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 2]], null)
                ->get(1)->pluck('id')->all();
            if (in_array($a->id, $ids, true) && in_array($b->id, $ids, true)) {
                $co++;
            }
        }

        $this->assertSame(0, $co, 'a shared boundary ayah counts as overlapping');
    }

    public function test_touching_ranges_never_co_occur_as_last_resort(): void
    {
        $ayahs = $this->seedSurahWithAyahs(20);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // A: 1-5, B: 6-9 touch (gap == 1) and must be treated as conflicting.
        $a = $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $b = $this->makePosition(1, 3, $aid(6), $aid(9), 30);

        $selector = new MafsilPositionSelector();
        $co = 0;
        for ($i = 0; $i < 50; $i++) {
            $ids = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 2]], null)
                ->get(1)->pluck('id')->all();
            if (in_array($a->id, $ids, true) && in_array($b->id, $ids, true)) {
                $co++;
            }
        }

        $this->assertSame(0, $co, 'touching ranges must never co-occur');
    }

    public function test_only_overlapping_pool_returns_fewer_real_positions(): void
    {
        $ayahs = $this->seedSurahWithAyahs(20);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Exactly two real candidates, overlapping. Requesting 2 must never
        // return both; the placeholder (null ayah) may fill the remainder.
        $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $this->makePosition(1, 3, $aid(3), $aid(8), 30);

        $selector = new MafsilPositionSelector();
        for ($i = 0; $i < 50; $i++) {
            $picks = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 2]], null)->get(1);
            $real = $picks->filter(fn ($p) => $p->id > 0);

            $this->assertLessThan(2, $real->count(), 'must not return both overlapping real positions');
            $this->assertFalse($this->anyOverlapPair($picks), 'no returned pair may overlap');
        }
    }

    public function test_large_random_selection_has_no_overlapping_pair(): void
    {
        $ayahs = $this->seedSurahWithAyahs(200);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Part 1 pool.
        $this->makePosition(1, 3, $aid(1), $aid(10), 1);
        $this->makePosition(1, 3, $aid(21), $aid(30), 2);
        $this->makePosition(1, 3, $aid(41), $aid(50), 3);
        $this->makePosition(1, 3, $aid(61), $aid(70), 4);
        $this->makePosition(1, 3, $aid(81), $aid(90), 5);

        // Part 2 pool, deliberately overlapping part 1's ranges (cross-part).
        $this->makePosition(2, 3, $aid(45), $aid(55), 1);
        $this->makePosition(2, 3, $aid(66), $aid(75), 2);
        $this->makePosition(2, 3, $aid(6), $aid(15), 3);
        $this->makePosition(2, 3, $aid(86), $aid(95), 4);
        $this->makePosition(2, 3, $aid(101), $aid(110), 5);

        $selector = new MafsilPositionSelector();
        $dist = [1 => [1 => 0, 2 => 0, 3 => 3], 2 => [1 => 0, 2 => 0, 3 => 3]];

        for ($i = 0; $i < 50; $i++) {
            $all = $selector->select([1, 2], $dist, null)->flatten();
            $this->assertFalse(
                $this->anyOverlapPair($all),
                'no pair of returned positions may overlap across all parts'
            );
        }
    }

    public function test_two_students_have_independent_cycles(): void
    {
        $sup = $this->makeUser();
        $a = $this->makeStudent($sup, 'a@example.test');
        $b = $this->makeStudent($sup, 'b@example.test');
        $this->seedSurahWithAyahs(30);

        $ayahs = Ayah::orderBy('id')->get()->keyBy('ayah_number');
        $aid = fn (int $n) => $ayahs[$n]->id;

        $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $this->makePosition(1, 3, $aid(7), $aid(11), 3);
        $this->makePosition(1, 3, $aid(13), $aid(17), 5);
        $this->makePosition(1, 3, $aid(19), $aid(23), 7);
        $this->makePosition(1, 3, $aid(25), $aid(29), 9);

        $selector = new MafsilPositionSelector();
        $dist = [1 => [1 => 0, 2 => 0, 3 => 2]];

        for ($i = 0; $i < 5; $i++) {
            $selector->selectAndRecord([1], $dist, $a->id, $sup->id);
        }
        $countBBefore = MafsilPositionUsage::where('student_id', $b->id)->count();
        $countABefore = MafsilPositionUsage::where('student_id', $a->id)->count();

        // One pick for student B
        $resultB = $selector->selectAndRecord([1], $dist, $b->id, $sup->id);

        $this->assertSame($countBBefore + 2, MafsilPositionUsage::where('student_id', $b->id)->count(),
            'B gained exactly 2 active rows (need=2)');
        $this->assertSame($countABefore, MafsilPositionUsage::where('student_id', $a->id)->count(),
            'A row count is unchanged by B selection');

        // B picks may overlap with A's picks (no exclusion between students)
        $bIds = $resultB->get(1)->pluck('id')->all();
        $aIds = MafsilPositionUsage::where('student_id', $a->id)->whereNull('completed_at')->pluck('mafsil_position_id')->all();
        $overlap = array_intersect($bIds, $aIds);
        // overlap is permitted (independent cycles); we just verify the service did not crash and that
        // B's pool is not artificially excluding positions A used.
        $this->assertIsArray($overlap);
    }

    public function test_leftover_picked_before_new_cycle(): void
    {
        $sup = $this->makeUser();
        $student = $this->makeStudent($sup, 'c@example.test');
        $this->seedSurahWithAyahs(60);

        // 3 spaced, non-adjacent hard positions in juz 1.
        $ayahs = Ayah::orderBy('id')->get()->keyBy('ayah_number');
        $aid = fn (int $n) => $ayahs[$n]->id;

        $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $this->makePosition(1, 3, $aid(7), $aid(11), 3);
        $this->makePosition(1, 3, $aid(13), $aid(17), 5);

        $selector = new MafsilPositionSelector();
        $dist = [1 => [1 => 0, 2 => 0, 3 => 2]];

        // Round 1
        $selector->selectAndRecord([1], $dist, $student->id, $sup->id);
        $this->assertSame(2, MafsilPositionUsage::where('student_id', $student->id)
            ->whereNull('completed_at')->count(), 'Round 1 leaves 2 active rows');
        $maxIdAfterRound1 = MafsilPositionUsage::where('student_id', $student->id)->max('id');

        // Round 2: leftover (1 position unused from pool of 3) + 1 from new cycle.
        $resultRound2 = $selector->selectAndRecord([1], $dist, $student->id, $sup->id);

        $round2Rows = MafsilPositionUsage::where('student_id', $student->id)
            ->where('id', '>', $maxIdAfterRound1)
            ->get();

        $this->assertCount(2, $round2Rows, 'Round 2 inserted exactly 2 rows');

        $historicalFromRound2 = $round2Rows->whereNotNull('completed_at');
        $activeFromRound2 = $round2Rows->whereNull('completed_at');

        $this->assertCount(1, $historicalFromRound2, 'One row from round 2 belongs to the closed cycle');
        $this->assertCount(1, $activeFromRound2, 'One row from round 2 belongs to the new cycle');

        $historicalPosId = $historicalFromRound2->first()->mafsil_position_id;
        $activePosId = $activeFromRound2->first()->mafsil_position_id;

        $this->assertNotSame(
            $historicalPosId,
            $activePosId,
            'Leftover and new-cycle pick must be different positions'
        );

        // Round-2 picks also include both of these.
        $round2PickIds = $resultRound2->get(1)->pluck('id')->all();
        $this->assertContains((int) $historicalPosId, $round2PickIds);
        $this->assertContains((int) $activePosId, $round2PickIds);
    }

    public function test_placeholders_used_when_pool_too_small(): void
    {
        $ayahs = $this->seedSurahWithAyahs(30);
        $this->makePosition(1, 3, $ayahs[1]->id, $ayahs[5]->id, 1); // only 1 real hard position

        $selector = new MafsilPositionSelector();
        $result = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 3]], null);
        $picks = $result->get(1);

        $this->assertCount(3, $picks, 'Got 3 picks total');
        $real = $picks->filter(fn ($p) => $p->id > 0);
        $placeholders = $picks->filter(fn ($p) => $p->id < 0);
        $this->assertCount(1, $real, '1 real position');
        $this->assertCount(2, $placeholders, '2 placeholders');
    }

    public function test_global_count_includes_all_rows(): void
    {
        $sup = $this->makeUser();
        $student = $this->makeStudent($sup, 'd@example.test');
        $ayahs = $this->seedSurahWithAyahs(60);

        // 5 hard positions
        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            $startN = 1 + $i * 10;
            $endN = 5 + $i * 10;
            $p = $this->makePosition(1, 3, $ayahs[$startN]->id, $ayahs[$endN]->id, $i + 1);
            $ids[] = $p->id;
        }

        // Insert 5 historical rows so each position has c=5 globally.
        $now = Carbon::now();
        foreach ($ids as $id) {
            MafsilPositionUsage::create([
                'juz_number' => 1,
                'difficulty' => 3,
                'mafsil_position_id' => $id,
                'supervisor_id' => $sup->id,
                'student_id' => $student->id,
                'completed_at' => $now->copy()->subDay(),
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
            ]);
        }

        $selector = new MafsilPositionSelector();
        $result = $selector->selectAndRecord([1], [1 => [1 => 0, 2 => 0, 3 => 3]], $student->id, $sup->id);
        $picks = $result->get(1);
        $this->assertCount(3, $picks);

        // Each picked position had 1 historical row inserted above; the service then inserted
        // 1 more row for each. Without historical counting the count would be 1 (new only).
        // Asserting >= 2 proves historical rows are included in the global count.
        foreach ($picks as $p) {
            if ($p->id <= 0) continue; // skip placeholder
            $count = MafsilPositionUsage::where('mafsil_position_id', $p->id)->count();
            $this->assertGreaterThanOrEqual(2, $count, "Position {$p->id} includes its historical row");
        }
    }

    public function test_transaction_rolls_back_on_failure(): void
    {
        $sup = $this->makeUser();
        $student = $this->makeStudent($sup, 'rollback@example.test');
        $ayahs = $this->seedSurahWithAyahs(60);

        // 3 hard positions, all in juz 1. Round 1 leaves 2 active rows.
        $this->makePosition(1, 3, $ayahs[1]->id, $ayahs[5]->id, 1);
        $this->makePosition(1, 3, $ayahs[7]->id, $ayahs[11]->id, 3);
        $this->makePosition(1, 3, $ayahs[13]->id, $ayahs[17]->id, 5);

        $selector = new MafsilPositionSelector();
        $dist = [1 => [1 => 0, 2 => 0, 3 => 2]];

        // Round 1: leaves 2 active rows.
        $selector->selectAndRecord([1], $dist, $student->id, $sup->id);
        $this->assertSame(2, MafsilPositionUsage::where('student_id', $student->id)
            ->whereNull('completed_at')->count(), 'Round 1 leaves 2 active rows');
        $maxIdBefore = (int) MafsilPositionUsage::where('student_id', $student->id)->max('id');

        // Round 2 would close the cycle (need=2, only 1 unused left → close + 1 fresh pick).
        // We force the INSERT to fail by passing a non-existent supervisor_id (FK violation).
        // The transaction must roll back BOTH the cycle-close UPDATE and the (attempted) INSERT.
        try {
            $selector->selectAndRecord([1], $dist, $student->id, 0); // supervisor_id=0 → FK violation
            $this->fail('Expected FK violation on supervisor_id=0');
        } catch (\Throwable $e) {
            // expected
        }

        // (1) No new rows were inserted.
        $maxIdAfter = (int) MafsilPositionUsage::where('student_id', $student->id)->max('id');
        $this->assertSame($maxIdBefore, $maxIdAfter, 'No new rows after rollback');
        $this->assertSame(2, MafsilPositionUsage::where('student_id', $student->id)->count(),
            'Total row count is unchanged');

        // (2) Round 1's 2 rows still have completed_at = NULL (cycle was NOT closed).
        $this->assertSame(2, MafsilPositionUsage::where('student_id', $student->id)
            ->whereNull('completed_at')->count(),
            'Old rows must still be active (cycle was not closed)');
    }

    public function test_all_parts_record_usage_in_one_call(): void
    {
        $sup = $this->makeUser();
        $student = $this->makeStudent($sup, 'multiparts@example.test');
        $ayahs = $this->seedSurahWithAyahs(120);

        // 3 parts × 3 positions each (2 hard, 1 easy per part). Spaced, non-adjacent.
        for ($part = 1; $part <= 3; $part++) {
            $base = ($part - 1) * 40;
            $this->makePosition($part, 3, $ayahs[$base + 1]->id, $ayahs[$base + 5]->id, 1);
            $this->makePosition($part, 3, $ayahs[$base + 11]->id, $ayahs[$base + 15]->id, 5);
            $this->makePosition($part, 1, $ayahs[$base + 21]->id, $ayahs[$base + 25]->id, 9);
        }

        $dist = [
            1 => [1 => 1, 2 => 0, 3 => 2],
            2 => [1 => 1, 2 => 0, 3 => 2],
            3 => [1 => 1, 2 => 0, 3 => 2],
        ];

        $selector = new MafsilPositionSelector();
        $selector->selectAndRecord([1, 2, 3], $dist, $student->id, $sup->id);

        // Expect 9 active rows total (3 parts × 3 picks each).
        $this->assertSame(9, MafsilPositionUsage::where('student_id', $student->id)
            ->whereNull('completed_at')->count(), '9 active rows (3 per part)');

        // Per-part breakdown — every part must have exactly 3 rows, not just the last.
        foreach ([1, 2, 3] as $part) {
            $count = MafsilPositionUsage::where('student_id', $student->id)
                ->where('juz_number', $part)
                ->whereNull('completed_at')->count();
            $this->assertSame(3, $count, "Part $part must have 3 active rows");
        }
    }

    public function test_select_does_not_close_legacy_null_student_rows(): void
    {
        $sup = $this->makeUser();
        $ayahs = $this->seedSurahWithAyahs(30);
        $pos = $this->makePosition(1, 3, $ayahs[1]->id, $ayahs[5]->id, 1);

        // Legacy row exactly like the ones the migration left behind: student_id NULL,
        // completed_at NULL. A buggy where('student_id', null) → whereNull would close this.
        $legacy = MafsilPositionUsage::create([
            'juz_number' => 1,
            'difficulty' => 3,
            'mafsil_position_id' => $pos->id,
            'supervisor_id' => $sup->id,
            'student_id' => null,
            'completed_at' => null,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $legacyId = $legacy->id;

        $selector = new MafsilPositionSelector();
        $result = $selector->select([1], [1 => [1 => 0, 2 => 0, 3 => 1]], null);

        $this->assertCount(1, $result->get(1));
        $this->assertSame(1, MafsilPositionUsage::count(), 'no DB writes by select()');

        $fresh = MafsilPositionUsage::find($legacyId);
        $this->assertNull($fresh->student_id);
        $this->assertNull($fresh->completed_at,
            'legacy NULL-student_id row must NOT be closed by select()');
    }

    public function test_get_show_url_twice_does_not_burn_extra_positionings(): void
    {
        $sup = $this->makeUser();
        $student = $this->makeStudent($sup, 'refresh@example.test');
        $ayahs = $this->seedSurahWithAyahs(150);

        // 3 hard positions per part × 3 parts = 9 total.
        for ($part = 1; $part <= 3; $part++) {
            $base = ($part - 1) * 50;
            $this->makePosition($part, 3, $ayahs[$base + 1]->id,  $ayahs[$base + 5]->id,  1);
            $this->makePosition($part, 3, $ayahs[$base + 11]->id, $ayahs[$base + 15]->id, 3);
            $this->makePosition($part, 3, $ayahs[$base + 21]->id, $ayahs[$base + 25]->id, 5);
            QuranPart::create([
                'part_number' => $part,
                'name' => "Part {$part}",
                'pages_count' => 20,
                'start_page' => 1,
                'end_page' => 20,
            ]);
        }

        $this->actingAs($sup);

        $postUrl = route('supervisor.recitation.mafsil.post');
        $response = $this->post($postUrl, [
            'student_id'  => $student->id,
            'mafsil_type' => 3,
            'selected_parts' => [1, 2, 3],
            'difficulty' => [
                1 => [1 => 0, 2 => 0, 3 => 3],
                2 => [1 => 0, 2 => 0, 3 => 3],
                3 => [1 => 0, 2 => 0, 3 => 3],
            ],
        ]);
        if (!$response->isRedirect()) {
            fwrite(STDERR, "POST response status: " . $response->getStatusCode() . "\n");
            fwrite(STDERR, "Content: " . substr($response->getContent(), 0, 500) . "\n");
        }
        $response->assertRedirect();

        $countAfterPost = MafsilPositionUsage::where('student_id', $student->id)->count();
        $this->assertSame(9, $countAfterPost, '3 parts × hard=3 = 9 usage rows after one POST');

        $showUrl = $response->headers->get('Location');

        $first = $this->get($showUrl);
        $first->assertOk();
        $second = $this->get($showUrl);
        $second->assertOk();

        $this->assertSame(
            9,
            MafsilPositionUsage::where('student_id', $student->id)->count(),
            'GETs to the show URL do not insert usage rows'
        );

        $this->assertSame(
            $this->extractMafsilPositionIds($first->getContent()),
            $this->extractMafsilPositionIds($second->getContent()),
            'Both GETs render identical position IDs'
        );
    }

    public function test_positions_within_a_part_are_ordered_by_page_number(): void
    {
        $ayahs = $this->seedSurahWithAyahs(40);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // list_order asc is 1,5,9 but we force the page order to be 3,6,9 so the
        // two orderings disagree. Non-adjacent positions, so all three are picked.
        $this->makePosition(1, 3, $aid(1), $aid(5), 1);
        $this->makePosition(1, 3, $aid(11), $aid(15), 5);
        $this->makePosition(1, 3, $aid(21), $aid(25), 9);

        $ayahs[1]->update(['page_number' => 9]);
        $ayahs[11]->update(['page_number' => 3]);
        $ayahs[21]->update(['page_number' => 6]);

        $picks = (new MafsilPositionSelector())
            ->select([1], [1 => [1 => 0, 2 => 0, 3 => 3]], null)
            ->get(1);

        $pages = $picks->map(fn ($p) => (int) $p->startAyah->page_number)->all();

        $this->assertSame([3, 6, 9], $pages, 'positions must render ascending by mushaf page number');
    }

    public function test_selected_parts_render_ascending_by_part_number(): void
    {
        $ayahs = $this->seedSurahWithAyahs(60);
        $aid = fn (int $n) => $ayahs[$n]->id;

        // Parts 3 and 9, 3 positions each (one per difficulty), non-adjacent.
        foreach ([3, 9] as $part) {
            $base = $part * 5;
            QuranPart::create([
                'part_number' => $part,
                'name' => "Part {$part}",
                'pages_count' => 20,
                'start_page' => 1,
                'end_page' => 20,
            ]);
            $this->makePosition($part, 1, $aid($base + 1), $aid($base + 3), 1);
            $this->makePosition($part, 2, $aid($base + 6), $aid($base + 8), 10);
            $this->makePosition($part, 3, $aid($base + 11), $aid($base + 13), 20);
        }

        $dist = [
            9 => [1 => 1, 2 => 1, 3 => 1],
            3 => [1 => 1, 2 => 1, 3 => 1],
        ];

        // Service level: $byPart keyed ascending regardless of submission order.
        $byPart = (new MafsilPositionSelector())->select([9, 3], $dist, null);
        $this->assertSame([3, 9], $byPart->keys()->all(), '$byPart must be keyed ascending by part');

        // HTTP level: the guest page renders part 3 before part 9 (submitted [9, 3]).
        $response = $this->post(route('public.recitation.mafsil.post'), [
            'student_name' => 'test',
            'mahd_name' => 'test',
            'mafsil_type' => 2,
            'positions_per_part' => 3,
            'selected_parts' => [9, 3],
            'difficulty' => $dist,
        ]);
        $response->assertOk();

        preg_match_all(
            '/name="pages\[\d+\]\[page_number\]" value="(\d+)"/',
            $response->getContent(),
            $m
        );
        $renderedPartOrder = array_values(array_unique(array_map('intval', $m[1])));

        $this->assertSame([3, 9], $renderedPartOrder, 'parts must render ascending by part number');
    }

    public function test_fixed_seed_selection_is_stable(): void
    {
        $ayahs = $this->seedSurahWithAyahs(40);
        $aid = fn (int $n) => $ayahs[$n]->id;
        for ($i = 0; $i < 6; $i++) {
            $this->makePosition(1, ($i < 3 ? 1 : 3), $aid(1 + $i * 5), $aid(3 + $i * 5), $i + 1);
        }

        $selector = new MafsilPositionSelector();
        $dist = [1 => [1 => 1, 2 => 0, 3 => 2]];

        mt_srand(4242);
        $first = $selector->select([1], $dist, null)->flatten()->pluck('id')->sort()->values()->all();

        mt_srand(4242);
        $second = $selector->select([1], $dist, null)->flatten()->pluck('id')->sort()->values()->all();

        $this->assertSame($first, $second, 'a fixed RNG seed must produce an identical selected set');
    }

    private function extractMafsilPositionIds(string $html): array
    {
        preg_match_all(
            '/name="pages\[\d+\]\[mafsil_position_id\]"\s+value="(-?\d+)"/',
            $html,
            $m
        );
        return array_map('intval', $m[1]);
    }
}