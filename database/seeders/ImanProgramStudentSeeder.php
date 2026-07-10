<?php

namespace Database\Seeders;

use App\Models\{ImanCriterion, ImanDailyEntry, ImanProgram, Student, User};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImanProgramStudentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $this->command->error('لا يوجد مستخدم مدير. شغّل DatabaseSeeder أولاً.');
            return;
        }

        $students = Student::with('user')->get();
        if ($students->isEmpty()) {
            $this->command->error('لا يوجد طلاب. شغّل DatabaseSeeder أولاً.');
            return;
        }

        $programTemplates = [
            [
                'name' => 'برنامج التلاوة والحفظ اليومي',
                'description' => 'متابعة التلاوة والحفظ اليومي للقرآن الكريم',
                'criteria' => [
                    ['name' => 'قراءة جزء من القرآن', 'type' => 'check'],
                    ['name' => 'حفظ صفحات جديدة', 'type' => 'number', 'target_value' => 2],
                    ['name' => 'مراجعة الحفظ السابق', 'type' => 'check'],
                ],
            ],
            [
                'name' => 'برنامج الأذكار والأدعية',
                'description' => 'المداومة على أذكار الصباح والمساء',
                'criteria' => [
                    ['name' => 'أذكار الصباح', 'type' => 'check'],
                    ['name' => 'أذكار المساء', 'type' => 'check'],
                    ['name' => 'أذكار النوم', 'type' => 'check'],
                    ['name' => 'الاستغفار (مرة)', 'type' => 'number', 'target_value' => 100],
                ],
            ],
            [
                'name' => 'برنامج الصلاة على وقتها',
                'description' => 'الحفاظ على الصلوات الخمس في وقتها',
                'criteria' => [
                    ['name' => 'صلاة الفجر في وقتها', 'type' => 'check'],
                    ['name' => 'صلاة الظهر في وقتها', 'type' => 'check'],
                    ['name' => 'صلاة العصر في وقتها', 'type' => 'check'],
                    ['name' => 'صلاة المغرب في وقتها', 'type' => 'check'],
                    ['name' => 'صلاة العشاء في وقتها', 'type' => 'check'],
                    ['name' => 'ركعات السنة الرواتب', 'type' => 'number', 'target_value' => 12],
                ],
            ],
            [
                'name' => 'برنامج قيام الليل والصدقة',
                'description' => 'الاجتهاد في قيام الليل والصدقة',
                'criteria' => [
                    ['name' => 'صلاة الوتر', 'type' => 'check'],
                    ['name' => 'قيام الليل', 'type' => 'check'],
                    ['name' => 'صدقة يومية', 'type' => 'check'],
                ],
            ],
        ];

        $today = Carbon::today();

        DB::transaction(function () use ($students, $programTemplates, $admin, $today) {
            foreach ($students as $index => $student) {
                $studentPrograms = $programTemplates;

                if ($index % 2 === 0) {
                    $studentPrograms = array_slice($programTemplates, 0, 2);
                } else {
                    $studentPrograms = array_slice($programTemplates, 0, 3);
                }

                if ($index === 0) {
                    $studentPrograms = array_slice($programTemplates, 0, 4);
                }

                $consistency = 0.45 + fmod($index * 0.07, 0.5);
                $consistency = min(0.95, $consistency);

                foreach ($studentPrograms as $pIdx => $template) {
                    $program = ImanProgram::create([
                        'name' => $template['name'],
                        'description' => $template['description'],
                        'created_by' => $admin->id,
                        'owner_id' => $student->id,
                        'owner_type' => $student->getMorphClass(),
                        'is_active' => $pIdx === 0,
                        'start_date' => $today->copy()->subDays(30),
                    ]);

                    foreach ($template['criteria'] as $cIdx => $c) {
                        $criterion = ImanCriterion::create([
                            'iman_program_id' => $program->id,
                            'name' => $c['name'],
                            'type' => $c['type'],
                            'target_value' => $c['target_value'] ?? null,
                            'sort_order' => $cIdx,
                        ]);

                        for ($d = 30; $d >= 0; $d--) {
                            $date = $today->copy()->subDays($d);
                            $dailyFactor = $consistency + (mt_rand(-15, 15) / 100);
                            $dailyFactor = max(0.1, min(1.0, $dailyFactor));

                            if (mt_rand() / mt_getrandmax() > $dailyFactor) {
                                continue;
                            }

                            $data = [
                                'iman_program_id' => $program->id,
                                'iman_criterion_id' => $criterion->id,
                                'entry_date' => $date->toDateString(),
                            ];

                            if ($criterion->isCheckType()) {
                                $data['is_checked'] = true;
                                $data['value'] = null;
                            } else {
                                $target = $criterion->target_value ?? 1;
                                $min = max(1, (int) floor($target * 0.5));
                                $value = mt_rand($min, $target);
                                $data['value'] = $value;
                                $data['is_checked'] = false;
                            }

                            ImanDailyEntry::create($data);
                        }
                    }
                }
            }
        });

        $programCount = ImanProgram::where('owner_type', (new Student)->getMorphClass())->count();
        $entryCount = ImanDailyEntry::count();
        $studentWithPrograms = ImanProgram::where('owner_type', (new Student)->getMorphClass())
            ->distinct('owner_id')
            ->count('owner_id');

        $this->command->info('✅ تم إنشاء بيانات البرامج الإيمانية للطلاب بنجاح!');
        $this->command->table(
            ['البند', 'العدد'],
            [
                ['عدد الطلاب الذين لديهم برامج', $studentWithPrograms],
                ['إجمالي البرامج', $programCount],
                ['إجمالي الإدخالات اليومية', $entryCount],
            ]
        );
    }
}
