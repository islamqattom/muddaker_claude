<?php

namespace App\Console\Commands;

use App\Models\MonthlyFee;
use App\Models\ParentProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BillingGenerate extends Command
{
    protected $signature = 'billing:generate {--year=} {--month=}';
    protected $description = 'Generate monthly fee records for all parent-student relationships';

    public function handle(): int
    {
        $year = $this->option('year') ?? now()->year;
        $month = $this->option('month') ?? now()->month;

        $this->info("Generating billing records for {$month}/{$year}...");

        // Pre-fetch existing fees for the period to avoid N+1
        $existingFees = MonthlyFee::forPeriod($year, $month)
            ->get()
            ->mapWithKeys(fn($f) => [$f->parent_id . '-' . $f->student_id => true]);

        $parents = ParentProfile::with(['students.feeCategory'])->get();
        $created = 0;
        $skipped = 0;
        $failed = 0;
        $unassigned = 0;

        foreach ($parents as $parent) {
            foreach ($parent->students as $student) {
                $key = $parent->id . '-' . $student->id;

                if (isset($existingFees[$key])) {
                    $skipped++;
                    continue;
                }

                $feeAmount = $student->feeCategory?->amount ?? 0;

                if ($feeAmount <= 0) {
                    $unassigned++;
                    Log::warning('Student has no fee category', [
                        'student_id' => $student->id,
                        'year' => $year,
                        'month' => $month,
                    ]);
                    continue;
                }

                try {
                    MonthlyFee::create([
                        'parent_id' => $parent->id,
                        'student_id' => $student->id,
                        'year' => $year,
                        'month' => $month,
                        'status' => MonthlyFee::STATUS_UNPAID,
                        'fee_amount' => $feeAmount,
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    Log::error('Failed to create monthly fee record', [
                        'parent_id' => $parent->id,
                        'student_id' => $student->id,
                        'year' => $year,
                        'month' => $month,
                        'error' => $e->getMessage(),
                    ]);
                    $failed++;
                }
            }
        }

        $this->info("Done! Created: {$created}, Skipped (already exist): {$skipped}, Failed: {$failed}, Unassigned (no category): {$unassigned}");

        return Command::SUCCESS;
    }
}
