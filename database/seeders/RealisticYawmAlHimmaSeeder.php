<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\YawmAlHimmaSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RealisticYawmAlHimmaSeeder extends Seeder
{
    private array $surahByPage = [];

    public function run(): void
    {
        $students = Student::all();
        if ($students->isEmpty()) {
            $this->command?->info('No students found. Skipping.');
            return;
        }

        // Dates to seed
        $dates = [
            '2026-06-22', '2026-06-23', '2026-06-24', '2026-06-25',
        ];

        $supervisors = [
            'أحمد العلي', 'خالد السبيعي', 'فهد الدوسري', 'سعد الزهراني',
            'ماجد الحربي', 'عبدالرحمن القحطاني', 'نواف العتيبي', 'طلال الأنصاري',
        ];

        foreach ($dates as $date) {
            // Each day: 60-90% of students attend
            $attendees = $students->random(rand((int) ($students->count() * 0.6), (int) ($students->count() * 0.9)));

            foreach ($attendees as $student) {
                // Performance profile
                $profile = $this->performanceProfile();

                // Parts count weighted toward fewer parts
                $partsCount = $this->weightedPartsCount();
                $parts = $this->pickParts($partsCount);

                $pagesData = [];
                $totalErrors = 0;
                $totalHesitations = 0;

                foreach ($parts as $part) {
                    $pages = rand(10, 20);
                    [$partErrors, $partHesitations] = $this->errorsHesitationsForProfile($profile, $pages);
                    $totalErrors += $partErrors;
                    $totalHesitations += $partHesitations;

                    // Distribute errors/hesitations across pages roughly evenly
                    $errorSlots = $this->distribute($partErrors, $pages);
                    $hesitationSlots = $this->distribute($partHesitations, $pages);

                    for ($page = 1; $page <= $pages; $page++) {
                        $pagesData[] = [
                            'part' => $part,
                            'page' => $page,
                            'part_number' => $part,
                            'page_number' => $page,
                            'surah' => $this->randomSurah(),
                            'ayah' => rand(1, 50),
                            'errors' => $errorSlots[$page - 1],
                            'hesitations' => $hesitationSlots[$page - 1],
                        ];
                    }
                }

                YawmAlHimmaSession::create([
                    'supervisor_name' => $supervisors[array_rand($supervisors)],
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'parts' => $parts,
                    'pages_data' => $pagesData,
                    'total_errors' => $totalErrors,
                    'total_hesitations' => $totalHesitations,
                    'session_date' => $date,
                    'notes' => null,
                ]);
            }
        }

        $this->command?->info('Realistic Yawm Al-Himma data seeded successfully.');
    }

    private function performanceProfile(): string
    {
        $rand = mt_rand(1, 100);
        return match (true) {
            $rand <= 10 => 'excellent',   // 10% excellent
            $rand <= 35 => 'good',        // 25% good
            $rand <= 70 => 'average',     // 35% average
            $rand <= 90 => 'weak',         // 20% weak
            default => 'very_weak',        // 10% very weak
        };
    }

    private function weightedPartsCount(): int
    {
        $rand = mt_rand(1, 100);
        return match (true) {
            $rand <= 30 => 1,
            $rand <= 60 => 2,
            $rand <= 80 => 3,
            $rand <= 92 => 4,
            $rand <= 98 => 5,
            default => 6,
        };
    }

    private function pickParts(int $count): array
    {
        $all = range(1, 30);
        shuffle($all);
        $selected = array_slice($all, 0, $count);
        sort($selected);
        return array_values($selected);
    }

    private function errorsHesitationsForProfile(string $profile, int $pages): array
    {
        // Targets are total errors/hesitations per part so scoring is realistic.
        // Quality bonus = max(0, 10 - errors). Fluency = max(0, 10 - hesitations*0.5).
        return match ($profile) {
            'excellent' => [rand(0, 3), rand(0, 6)],
            'good' => [rand(2, 7), rand(4, 12)],
            'average' => [rand(6, 12), rand(10, 20)],
            'weak' => [rand(11, 20), rand(18, 35)],
            'very_weak' => [rand(18, 35), rand(30, 60)],
        };
    }

    private function distribute(int $total, int $slots): array
    {
        if ($slots <= 0) {
            return [];
        }
        if ($total <= 0) {
            return array_fill(0, $slots, 0);
        }

        $base = (int) floor($total / $slots);
        $remainder = $total % $slots;
        $result = array_fill(0, $slots, $base);

        // Spread remainder randomly so not all extra errors are at the start
        $indices = range(0, $slots - 1);
        shuffle($indices);
        for ($i = 0; $i < $remainder; $i++) {
            $result[$indices[$i]]++;
        }

        return $result;
    }

    private function randomSurah(): string
    {
        $surahs = [
            'الفاتحة', 'البقرة', 'آل عمران', 'النساء', 'المائدة', 'الأنعام', 'الأعراف', 'الأنفال',
            'التوبة', 'يونس', 'هود', 'يوسف', 'الرعد', 'إبراهيم', 'الحجر', 'النحل',
            'الإسراء', 'الكهف', 'مريم', 'طه', 'الأنبياء', 'الحج', 'المؤمنون', 'النور',
            'الفرقان', 'الشعراء', 'النمل', 'القصص', 'العنكبوت', 'الروم', 'لقمان', 'السجدة',
            'الأحزاب', 'سبأ', 'فاطر', 'يس', 'الصافات', 'ص', 'الزمر', 'غافر',
            'فصلت', 'الشورى', 'الزخرف', 'الدخان', 'الجاثية', 'الأحقاف', 'محمد', 'الفتح',
            'الحجرات', 'ق', 'الذاريات', 'الطور', 'النجم', 'القمر', 'الرحمن', 'الواقعة',
            'الحديد', 'المجادلة', 'الحشر', 'الممتحنة', 'الصف', 'الجمعة', 'المنافقون', 'التغابن',
            'الطلاق', 'التحريم', 'الملك', 'القلم', 'الحاقة', 'المعارج', 'نوح', 'الجن',
            'المزمل', 'المدثر', 'القيامة', 'الإنسان', 'المرسلات', 'النبأ', 'النازعات', 'عبس',
            'التكوير', 'الانفطار', 'المطففين', 'الانشقاق', 'البروج', 'الطارق', 'الأعلى', 'الغاشية',
            'الفجر', 'البلد', 'الشمس', 'الليل', 'الضحى', 'الشرح', 'التين', 'العلق',
            'القدر', 'البينة', 'الزلزلة', 'العاديات', 'القارعة', 'التكاثر', 'العصر', 'الهمزة',
            'الفيل', 'قريش', 'الماعون', 'الكوثر', 'الكافرون', 'النصر', 'المسد', 'الإخلاص',
            'الفلق', 'الناس',
        ];
        return $surahs[array_rand($surahs)];
    }
}
