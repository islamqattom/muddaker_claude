<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Student, Supervisor, ParentProfile, Level, QuranPart, QuranPage};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================
        // Seed Levels
        // ============================
        $levels = [
            ['name' => 'المستوى الأول',  'level_number' => 1, 'new_memorization_pages' => 3, 'review_pages' => 6,  'description' => 'مستوى المبتدئين'],
            ['name' => 'المستوى الثاني', 'level_number' => 2, 'new_memorization_pages' => 4, 'review_pages' => 8,  'description' => 'مستوى المتوسطين'],
            ['name' => 'المستوى الثالث', 'level_number' => 3, 'new_memorization_pages' => 5, 'review_pages' => 10, 'description' => 'مستوى المتقدمين'],
            ['name' => 'المستوى الرابع', 'level_number' => 4, 'new_memorization_pages' => 6, 'review_pages' => 12, 'description' => 'مستوى المتفوقين'],
        ];
        foreach ($levels as $level) {
            Level::create($level);
        }

        // ============================
        // Seed Quran Parts (30 Ajzaa) - based on Madinah Mushaf
        // ============================
        $partsData = [
            [1,'الجزء الأول',1,21],
            [2,'الجزء الثاني',22,41],
            [3,'الجزء الثالث',42,61],
            [4,'الجزء الرابع',62,81],
            [5,'الجزء الخامس',82,101],
            [6,'الجزء السادس',102,121],
            [7,'الجزء السابع',122,141],
            [8,'الجزء الثامن',142,161],
            [9,'الجزء التاسع',162,181],
            [10,'الجزء العاشر',182,201],
            [11,'الجزء الحادي عشر',202,221],
            [12,'الجزء الثاني عشر',222,241],
            [13,'الجزء الثالث عشر',242,261],
            [14,'الجزء الرابع عشر',262,281],
            [15,'الجزء الخامس عشر',282,301],
            [16,'الجزء السادس عشر',302,321],
            [17,'الجزء السابع عشر',322,341],
            [18,'الجزء الثامن عشر',342,361],
            [19,'الجزء التاسع عشر',362,381],
            [20,'الجزء العشرون',382,401],
            [21,'الجزء الحادي والعشرون',402,421],
            [22,'الجزء الثاني والعشرون',422,441],
            [23,'الجزء الثالث والعشرون',442,461],
            [24,'الجزء الرابع والعشرون',462,481],
            [25,'الجزء الخامس والعشرون',482,501],
            [26,'الجزء السادس والعشرون',502,521],
            [27,'الجزء السابع والعشرون',522,541],
            [28,'الجزء الثامن والعشرون',542,561],
            [29,'الجزء التاسع والعشرون',562,581],
            [30,'الجزء الثلاثون',582,604],
        ];

        foreach ($partsData as [$num, $name, $start, $end]) {
            $part = QuranPart::create([
                'part_number' => $num,
                'name' => $name,
                'pages_count' => $end - $start + 1,
                'start_page' => $start,
                'end_page' => $end,
            ]);

            // Create pages for each part
            for ($page = $start; $page <= $end; $page++) {
                QuranPage::create([
                    'part_id' => $part->id,
                    'page_number' => $page,
                    'page_in_part' => $page - $start + 1,
                ]);
            }
        }

        // ============================
        // Admin User
        // ============================
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@quran.com',
            'phone' => '0501000001',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // ============================
        // Supervisor 1
        // ============================
        $supUser1 = User::create([
            'name' => 'الأستاذ أحمد محمد',
            'email' => 'ahmed@quran.com',
            'phone' => '0501000002',
            'password' => Hash::make('Super@123'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);
        Supervisor::create([
            'user_id' => $supUser1->id,
            'employee_number' => 'EMP-001',
            'specialization' => 'تحفيظ القرآن الكريم',
            'hire_date' => '2020-01-01',
        ]);

        // Supervisor 2
        $supUser2 = User::create([
            'name' => 'الأستاذ محمود علي',
            'email' => 'mahmoud@quran.com',
            'phone' => '0501000003',
            'password' => Hash::make('Super@123'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);
        Supervisor::create([
            'user_id' => $supUser2->id,
            'employee_number' => 'EMP-002',
            'specialization' => 'تحفيظ القرآن الكريم',
            'hire_date' => '2021-03-01',
        ]);

        // ============================
        // Students
        // ============================
        $studentNames = [
            'عبدالله أحمد الزيد', 'محمد سالم العتيبي', 'خالد عبدالرحمن النجدي',
            'يوسف إبراهيم الشمري', 'عمر فيصل القحطاني', 'سعد ناصر المطيري',
            'راشد خالد الربابعة', 'فهد عبدالله السعدي', 'أنس محمد الدوسري',
            'تركي سعد العنزي',
        ];

        foreach ($studentNames as $i => $name) {
            $supId = $i < 5 ? $supUser1->id : $supUser2->id;
            $levelId = ($i % 4) + 1;

            $stuUser = User::create([
                'name' => $name,
                'email' => 'student' . ($i+1) . '@quran.com',
                'phone' => '050200000' . ($i+1),
                'password' => Hash::make('Student@123'),
                'role' => 'student',
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $stuUser->id,
                'supervisor_id' => $supId,
                'level_id' => $levelId,
                'student_number' => 'STU-2024-' . str_pad($i+1, 4, '0', STR_PAD_LEFT),
                'birth_date' => '200' . ($i % 9 + 1) . '-01-01',
                'phone' => '050200000' . ($i+1),
                'address' => 'المدينة المنورة',
                'enrollment_date' => '2024-01-01',
                'current_part' => 1,
                'current_page' => $i * 5 + 1,
            ]);

            // Parent for each student
            $parentUser = User::create([
                'name' => 'والد ' . $name,
                'email' => 'parent' . ($i+1) . '@quran.com',
                'phone' => '050300000' . ($i+1),
                'password' => Hash::make('Parent@123'),
                'role' => 'parent',
                'is_active' => true,
            ]);
            $parent = ParentProfile::create([
                'user_id' => $parentUser->id,
                'relationship' => 'father',
            ]);

            $student = Student::where('user_id', $stuUser->id)->first();
            $parent->students()->attach($student->id);
        }

        $this->call(AzkarSeeder::class);
        $this->command->info('✅ تم إنشاء البيانات الأولية بنجاح!');
        $this->command->table(
            ['المستخدم', 'البريد الإلكتروني', 'كلمة المرور', 'الدور'],
            [
                ['مدير النظام', 'admin@quran.com', 'Admin@123', 'admin'],
                ['أحمد محمد', 'ahmed@quran.com', 'Super@123', 'supervisor'],
                ['محمود علي', 'mahmoud@quran.com', 'Super@123', 'supervisor'],
                ['عبدالله أحمد (طالب)', 'student1@quran.com', 'Student@123', 'student'],
                ['والد عبدالله (ولي)', 'parent1@quran.com', 'Parent@123', 'parent'],
            ]
        );
    }
}
