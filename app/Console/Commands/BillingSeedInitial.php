<?php

namespace App\Console\Commands;

use App\Models\MonthlyFee;
use App\Models\ParentProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BillingSeedInitial extends Command
{
    protected $signature = 'billing:seed-initial {--months=4}';
    protected $description = 'Seed initial monthly fee records for existing parent-student relationships';

    public function handle(): int
    {
        $monthsBack = (int) $this->option('months');

        $this->info("Seeding initial billing records for past {$monthsBack} months...");

        $parents = ParentProfile::with('students')->get();
        $created = 0;
        $skipped = 0;
        $failed = 0;

        $current = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $monthsBack; $i++) {
            $targetDate = $current->copy()->subMonths($i);
            $month = $targetDate->month;
            $year = $targetDate->year;

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
                        Log::error('Failed to create monthly fee record during seeding', [
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

            $this->line("Processed month {$month}/{$year}");
        }

        $this->info("Done! Created: {$created}, Skipped (already exist): {$skipped}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
