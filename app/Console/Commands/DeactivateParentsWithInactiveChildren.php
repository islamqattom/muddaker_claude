<?php

namespace App\Console\Commands;

use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\ParentActivationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeactivateParentsWithInactiveChildren extends Command
{
    protected $signature = 'parents:deactivate-inactive-children {--dry-run} {--force}';

    protected $description = 'List or apply automatic deactivation for parents whose linked children are all inactive';

    public function handle(ParentActivationService $parentActivation): int
    {
        $parents = ParentProfile::query()
            ->whereHas('user', fn ($q) => $q->where('role', 'parent'))
            ->with('user')
            ->get();

        $matches = [];
        foreach ($parents as $parent) {
            if (! $parent->user) {
                continue;
            }

            $students = $parent->students()->with('user')->get();

            // Never touch a parent with zero linked children.
            if ($students->isEmpty()) {
                continue;
            }

            $allInactive = $students->every(
                fn (Student $student) => ! optional($student->user)->is_active
            );

            if ($allInactive) {
                $matches[] = ['parent' => $parent, 'children' => $students->count()];
            }
        }

        if (empty($matches)) {
            $this->info('لا يوجد أولياء أمور جميع أبنائهم معطّلون.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'ولي الأمر', 'عدد الأبناء', 'الحالة'],
            array_map(function (array $row) {
                $user = $row['parent']->user;

                return [
                    $user->id,
                    $user->name,
                    $row['children'],
                    $user->is_active ? 'نشط' : 'معطّل',
                ];
            }, $matches)
        );

        if ($this->option('dry-run')) {
            $this->warn('وضع المعاينة: لم يتم إجراء أي تغييرات.');

            return self::SUCCESS;
        }

        if (! $this->option('force')
            && ! $this->confirm('هل تريد تعطيل حسابات أولياء الأمور هؤلاء تلقائيًا؟')) {
            $this->info('تم إلغاء العملية.');

            return self::SUCCESS;
        }

        $applied = DB::transaction(function () use ($matches, $parentActivation) {
            $count = 0;
            foreach ($matches as $row) {
                $event = $parentActivation->syncParentStatus($row['parent']);
                if ($event && $event['action'] === 'deactivated') {
                    $count++;
                }
            }

            return $count;
        });

        $this->info("تم تعطيل {$applied} حساب ولي أمر تلقائيًا.");

        return self::SUCCESS;
    }
}
