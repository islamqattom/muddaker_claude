<?php

namespace Database\Seeders;

use App\Models\TajweedRule;
use Illuminate\Database\Seeder;

class TajweedRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'مخرج حرف الألف',
                'description' => 'حرف الألف يُنطق من أقصى الحلق، من الحلقوم. وهو أبعد الحروف عن الفم.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 1,
                'example_verse_key' => '1:1',
                'examples' => [
                    'الآية الأولى من الفاتحة: بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
                    'سورة الإخلاص: قُلْ هُوَ اللَّهُ أَحَدٌ',
                ],
            ],
            [
                'name' => 'مخرج حرف الباء',
                'description' => 'حرف الباء يُنطق من الشفتين مع اجتماعهما، مع إخراج النفس.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 2,
                'example_verse_key' => '2:255',
                'examples' => [
                    'آية الكرسي: اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ',
                ],
            ],
            [
                'name' => 'مخرج حرف التاء',
                'description' => 'حرف التاء يُنطق من طرف اللسان مع أطراف الأسنان العليا.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 3,
                'example_verse_key' => '2:23',
                'examples' => [
                    'وَكَذَٰلِكَ أَنزَلْنَا إِلَيْكَ الْكِتَابَ',
                ],
            ],
            [
                'name' => 'مخرج حرف الثاء',
                'description' => 'حرف الثاء يُنطق من طرف اللسان مع أطراف الأسنان العليا، وهو حرف بين الأسنان.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 4,
                'example_verse_key' => '3:18',
                'examples' => [
                    'شَهِدَ اللَّهُ أَنَّهُ لَا إِلَٰهَ إِلَّا هُوَ',
                ],
            ],
            [
                'name' => 'مخرج حرف الجيم',
                'description' => 'حرف الجيم يُنطق من مخرج بين الحلق والوسط، من اللسان مع الحلق.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 5,
                'example_verse_key' => '2:46',
                'examples' => [
                    'الَّذِينَ يَخْشَوْنَ رَبَّهُم بِالْغَيْبِ',
                ],
            ],
            [
                'name' => 'مخرج حرف الحاء',
                'description' => 'حرف الحاء يُنطق من أقصى الحلق، من مخرج بين القاف والخاء.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 6,
                'example_verse_key' => '1:1',
                'examples' => [
                    'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ',
                ],
            ],
            [
                'name' => 'مخرج حرف الخاء',
                'description' => 'حرف الخاء يُنطق من أقصا الحلق من مخرج واحد مع الغين.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 7,
                'example_verse_key' => '1:1',
                'examples' => [
                    'الرَّحْمَٰنِ الرَّحِيمِ',
                ],
            ],
            [
                'name' => 'مخرج حرف الدال',
                'description' => 'حرف الدال يُنطق من طرف اللسان مع أطراف الأسنان العليا.',
                'category' => TajweedRule::CATEGORY_MAKHRAJ,
                'order' => 8,
                'example_verse_key' => '2:47',
                'examples' => [
                    'يَا بَنِي إِسْرَائِيلَ',
                ],
            ],
            [
                'name' => 'الإدغام الكامل',
                'description' => 'الإدغام الكامل (المعروف بالحلّ) يكون بين حرفين متقاربين في المخرج والصفة، يُدغَم الأول في الثاني مع الغُنّة.',
                'category' => TajweedRule::CATEGORY_IDGHAM,
                'order' => 20,
                'example_verse_key' => '2:58',
                'examples' => [
                    'مِمَّا خَطِيئَاتِهِمْ أُغْرِقُوا',
                    'مَنْ عَمِلَ صَالِحًا',
                ],
            ],
            [
                'name' => 'الإدغام النصفي',
                'description' => 'الإدغام النصفي يكون بين حرفين متقاربين، يُدغَم الأول في الثاني بدون غُنّة.',
                'category' => TajweedRule::CATEGORY_IDGHAM,
                'order' => 21,
                'example_verse_key' => '1:1',
                'examples' => [
                    'بِسْمِ اللَّهِ',
                ],
            ],
            [
                'name' => 'القلقلة',
                'description' => 'القلقلة هي نطق الحرف بحركة قوية مضطرمة عند الوقف، والحروف التي تقبل القلقلة: ق، ط، ب، ج.',
                'category' => TajweedRule::CATEGORY_QALQALA,
                'order' => 30,
                'example_verse_key' => '2:74',
                'examples' => [
                    'أَوْ يَأْتِيَهُمْ عَذَابٌ غَيْرُ مُهْتَدٍ',
                ],
            ],
            [
                'name' => 'وقف الابتداد',
                'description' => 'وقف الابتداد هو أن يتوقف القارئ عند كلمة ثم يبتدئ من كلمة أخرى، ولا يُشروع.',
                'category' => TajweedRule::CATEGORY_WAQF,
                'order' => 40,
                'example_verse_key' => '2:1',
                'examples' => [
                    'الم (ذَرْهُمْ يَخُوضُوا)',
                ],
            ],
            [
                'name' => 'الوقف بالهاء',
                'description' => 'إذا جاء قبل الوقف هاء، وجب الوقف عليها، مثال: رَبِّي',
                'category' => TajweedRule::CATEGORY_WAQF,
                'order' => 41,
                'example_verse_key' => '1:8',
                'examples' => [
                    'صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ',
                ],
            ],
            [
                'name' => 'صفات الحروف: الجهر',
                'description' => 'الحروف المجهورة هي التي يُسمع لها صوت مع الجهر، وتشمل: أ، ب، ج، د، ذ، ر، ز، س، ش، ع، غ، ف، ق، ك، ه، و، ي.',
                'category' => TajweedRule::CATEGORY_SIFAT,
                'order' => 50,
                'example_verse_key' => '1:1',
                'examples' => [
                    'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ',
                ],
            ],
            [
                'name' => 'صفات الحروف: الهمس',
                'description' => 'الحروف المهموسة هي التي يُسمع لها صوت بدون جهر، وتشمل: ح، خ، س، ش، ص، ز، ف، ت، ث، ك، ه.',
                'category' => TajweedRule::CATEGORY_SIFAT,
                'order' => 51,
                'example_verse_key' => '2:58',
                'examples' => [
                    'خَطِيئَاتِهِمْ',
                ],
            ],
        ];

        foreach ($rules as $rule) {
            TajweedRule::create($rule);
        }
    }
}