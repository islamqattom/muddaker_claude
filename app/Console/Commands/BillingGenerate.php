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

        $parents = ParentProfile::with('students')->get();
        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($parents as $parent) {
            foreach ($parent->students as $student) {
                $exists = MonthlyFee::forParentStudent($parent->id, $student->id)
                    ->forPeriod($year, $month)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                try {
                    MonthlyFee::create([
                        'parent_id' => $parent->id,
                        'student_id' => $student->id,
                        'year' => $year,
                        'month' => $month,
                        'status' => MonthlyFee::STATUS_UNPAID,
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

        $this->info("Done! Created: {$created}, Skipped (already exist): {$skipped}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
