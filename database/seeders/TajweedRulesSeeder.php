<?php

namespace Database\Seeders;

use App\Models\TajweedRuleDefinition;
use Illuminate\Database\Seeder;

class TajweedRulesSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing
        TajweedRuleDefinition::query()->delete();

        $rules = [
            // ============================
            // مخارج الحروف (17 مخرجاً)
            // ============================
            [
                'name' => 'مخرج حرف الألف (الهمزة الساكنة)',
                'description' => 'أقصى الحلق، من الجوف. وهو أبعد الحروف عن الفم. يُنطق بانفتاح الحلقوم دون شدّة ولا ضغط.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 1,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف الباء',
                'description' => 'من الشفتين مع اجتماعهما برفق، مع خروج النفس. حرف شفويّ انفجاريّ مهموس.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 2,
                'example_verse_key' => '2:255',
                'examples' => ['اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ'],
            ],
            [
                'name' => 'مخرج حرف التاء',
                'description' => 'من طرف اللسان (أقصاه) مع أصول الثنايا العليا (الأسنان العليا). حرف لثويّ طبقيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 3,
                'example_verse_key' => '2:23',
                'examples' => ['وَكَذَٰلِكَ أَنزَلْنَا إِلَيْكَ الْكِتَابَ'],
            ],
            [
                'name' => 'مخرج حرف الثاء',
                'description' => 'من طرف اللسان مع أطراف الثنايا العليا، يدخل بينهما طرف اللسان قليلاً. حرف لثويّ أَسَلّيّ (بينيّ).',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 4,
                'example_verse_key' => '3:18',
                'examples' => ['شَهِدَ اللَّهُ أَنَّهُ لَا إِلَٰهَ إِلَّا هُوَ'],
            ],
            [
                'name' => 'مخرج حرف الجيم',
                'description' => 'وسط اللسان مع ما يُحاذيه من الحنك الأعلى. حرف لثويّ وسطيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 5,
                'example_verse_key' => '2:46',
                'examples' => ['الَّذِينَ يَظُنُّونَ أَنَّهُم مُّلَاقُو رَبِّهِمْ'],
            ],
            [
                'name' => 'مخرج حرف الحاء',
                'description' => 'أقصى وسط اللسان مع ما يُحاذيه من الحنك الأعلى. حرف لثويّ أقصى وسطيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 6,
                'example_verse_key' => '1:1',
                'examples' => ['الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ'],
            ],
            [
                'name' => 'مخرج حرف الخاء',
                'description' => 'أقصى اللسان مع ما يُحاذيه من الحنك الأعلى. حرف لثويّ أقصى. يشارك الغين في المخرج ويفارقها في الصفة (الخاء مهموسة).',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 7,
                'example_verse_key' => '1:1',
                'examples' => ['الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف الدال',
                'description' => 'من طرف اللسان مع أصول الثنايا العليا (أقرب من التاء). حرف لثويّ طبقيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 8,
                'example_verse_key' => '2:47',
                'examples' => ['يَا بَنِي إِسْرَائِيلَ'],
            ],
            [
                'name' => 'مخرج حرف الذال',
                'description' => 'من طرف اللسان مع أطراف الثنايا العليا (مثل الثاء ولكن بالجهر). حرف لثويّ أَسَلّيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 9,
                'example_verse_key' => '3:118',
                'examples' => ['لَا يَأْلُونَكُمْ خَبَالًا'],
            ],
            [
                'name' => 'مخرج حرف الراء',
                'description' => 'من طرف اللسان مع ما يُحاذيه من الحنك الأعلى (أقرب من الخاء). حرف لثويّ حافيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 10,
                'example_verse_key' => '1:1',
                'examples' => ['الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف الزاي',
                'description' => 'من طرف اللسان مع أطراف الثنايا السفلى. حرف لثويّ نيتيّ (بينيّ).',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 11,
                'example_verse_key' => '2:125',
                'examples' => ['وَاتَّخَذُوا مِن مَّقَامِ إِبْرَاهِيمَ مُصَلًّى'],
            ],
            [
                'name' => 'مخرج حرف السين',
                'description' => 'من طرف اللسان مع أصول الثنايا السفلى (أو يقال من بين طرف اللسان والثنايا السفلى). حرف لثويّ طبقيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 12,
                'example_verse_key' => '1:7',
                'examples' => ['صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ'],
            ],
            [
                'name' => 'مخرج حرف الشين',
                'description' => 'من وسط اللسان مع ما يُحاذيه من وسط الحنك الأعلى. حرف لثويّ وسطيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 13,
                'example_verse_key' => '2:185',
                'examples' => ['شَهْرُ رَمَضَانَ الَّذِي أُنزِلَ فِيهِ الْقُرْآنُ'],
            ],
            [
                'name' => 'مخرج حرف الصاد',
                'description' => 'من طرف اللسان مع أطراف الثنايا السفلى (أقرب من السين). حرف لثويّ أَسَلّيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 14,
                'example_verse_key' => '1:6',
                'examples' => ['اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ'],
            ],
            [
                'name' => 'مخرج حرف الضاد',
                'description' => 'من طرف اللسان مع أصول الثنايا العليا (من أقصاه). حرف لثويّ طبقيّ. يُضغط طرف اللسان على الثنايا العليا.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 15,
                'example_verse_key' => '2:27',
                'examples' => ['الَّذِينَ يَنقُضُونَ عَهْدَ اللَّهِ مِن بَعْدِ مِيثَاقِهِ'],
            ],
            [
                'name' => 'مخرج حرف الطاء',
                'description' => 'من طرف اللسان مع أصول الثنايا العليا (أقرب من الضاد). حرف لثويّ طبقيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 16,
                'example_verse_key' => '2:45',
                'examples' => ['وَاسْتَعِينُوا بِالصَّبْرِ وَالصَّلَاةِ'],
            ],
            [
                'name' => 'مخرج حرف الظاء',
                'description' => 'من طرف اللسان مع أطراف الثنايا العليا (مثل الذال مع الضغط). حرف لثويّ أَسَلّيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 17,
                'example_verse_key' => '2:19',
                'examples' => ['أَوْ كَصَيِّبٍ مِّنَ السَّمَاءِ فِيهِ ظُلُمَاتٌ'],
            ],
            [
                'name' => 'مخرج حرف العين',
                'description' => 'من أوسط الحلق (وسط الجوف). حرف حلقيّ أوسط. يُنطق بانضغاط الحلق الأوسط.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 18,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف الغين',
                'description' => 'من أقصى الحلق (أقصى الجوف). حرف حلقيّ أقصى. يشارك الخاء في المخرج ويفارقها بالجهر.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 19,
                'example_verse_key' => '2:35',
                'examples' => ['وَقُلْنَا يَا آدَمُ اسْكُنْ أَنتَ وَزَوْجُكَ الْجَنَّةَ'],
            ],
            [
                'name' => 'مخرج حرف الفاء',
                'description' => 'من طرف الشفتين السفلى مع أطراف الثنايا العليا. حرف شفويّ لثويّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 20,
                'example_verse_key' => '2:22',
                'examples' => ['فَاطِرَ السَّمَاوَاتِ وَالْأَرْضِ'],
            ],
            [
                'name' => 'مخرج حرف القاف',
                'description' => 'من أقصى اللسان مع ما يُحاذيه من الحنك الأعلى (أقرب من الكاف). حرف لثويّ أقصى.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 21,
                'example_verse_key' => '2:21',
                'examples' => ['يَا أَيُّهَا النَّاسُ اعْبُدُوا رَبَّكُمُ'],
            ],
            [
                'name' => 'مخرج حرف الكاف',
                'description' => 'من أقصى اللسان مع ما يُحاذيه من الحنك الأعلى (أقرب من القاف). حرف لثويّ أقصى.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 22,
                'example_verse_key' => '2:25',
                'examples' => ['وَبَشِّرِ الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ'],
            ],
            [
                'name' => 'مخرج حرف اللام',
                'description' => 'من طرف اللسان مع أصول الثنايا العليا (من أقربه). حرف لثويّ طبقيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 23,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف الميم',
                'description' => 'من الشفتين مع اجتماعهما برفق (مثل الباء مع الغُنّة). حرف شفويّ انفجاريّ مجهور.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 24,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف النون',
                'description' => 'من طرف اللسان مع أصول الثنايا العليا (من أقربه). حرف لثويّ طبقيّ.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 25,
                'example_verse_key' => '1:6',
                'examples' => ['اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ'],
            ],
            [
                'name' => 'مخرج حرف الهاء',
                'description' => 'من أدنى الحلق (قرب الفم). حرف حلقيّ أدنى.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 26,
                'example_verse_key' => '1:2',
                'examples' => ['الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ'],
            ],
            [
                'name' => 'مخرج حرف الواو',
                'description' => 'من الشفتين مع تدويرهما (مَضْمُومَتَيْنِ). حرف شفويّ لين.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 27,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'مخرج حرف الياء',
                'description' => 'من وسط اللسان مع ما يُحاذيه من الحنك الأعلى (مكسورتين). حرف لثويّ وسطيّ لين.',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 28,
                'example_verse_key' => '1:2',
                'examples' => ['الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ'],
            ],
            [
                'name' => 'مخرج حرف المد (الألف المدية)',
                'description' => 'من الجوف مع الفراغ. وهو حرف جوفيّ (لا يعتبر من مخارج الحروف الحقيقية لكنه مخرج للمد).',
                'category' => TajweedRuleDefinition::CATEGORY_MAKHRAJ,
                'order' => 29,
                'example_verse_key' => '1:1',
                'examples' => ['الرَّحْمَٰنِ الرَّحِيمِ'],
            ],

            // ============================
            // صفات الحروف
            // ============================
            [
                'name' => 'صفة الجهر',
                'description' => 'إعلاء الصوت بحيث يُسمع للحرف صوت عند النطق به. الحروف المجهورة: ب ج د ذ ر ز س ش ص ض ط ظ ع غ ف ق ك ه و ي (الجيم والشين والصاد والضاد والطاء والظاء ساكنة فقط).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 100,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'صفة الهمس',
                'description' => 'جريان النفس عند النطق بالحرف. الحروف المهموسة: ف ح ث ه ش خ ص س ك ت (عدا الجيم والشين والصاد والضاد والطاء والظاء).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 101,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'صفة الشدة (الاستعلاء)',
                'description' => 'منع مخرج الحرف من الانفتاح وضغطه. الحروف الشديدة: أجْدَ قِطْ بَكَتْ (ألف همزة-الجيم-الدال-القاف-الطاء-الباء-الكاف-التاء).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 102,
                'example_verse_key' => '2:2',
                'examples' => ['ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ'],
            ],
            [
                'name' => 'صفة الرخاوة',
                'description' => 'بقاء جزء من الصوت والنفس عند النطق بالحرف. الحروف الرخوة: باقي الحروف (عدا الشديدة واللينة).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 103,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'صفة الاستعلاء',
                'description' => 'ارتفاع اللسان إلى الحنك عند النطق بالحرف. الحروف المستعلية: خُصَّ ضَغْطٍ قِظْ (الخاء-الصاد-الضاد-الغين-الطاء-القاف-الظاء).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 104,
                'example_verse_key' => '1:1',
                'examples' => ['الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'صفة الاستفال',
                'description' => 'انخفاض اللسان عن الحنك. باقي الحروف غير المستعلية.',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 105,
                'example_verse_key' => '1:2',
                'examples' => ['الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ'],
            ],
            [
                'name' => 'صفة الإطباق',
                'description' => 'لصوق جزء من اللسان بما يُحاذيه من الحنك. الحروف المطبقة: صُطْبَعَضٍ (الصاد-الطاء-الباء-العين-الضاد).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 106,
                'example_verse_key' => '1:6',
                'examples' => ['اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ'],
            ],
            [
                'name' => 'صفة الانفتاح',
                'description' => 'بقاء اللسان منخفضاً عن الحنك. باقي الحروف غير المطبقة.',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 107,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'صفة الإذلاق',
                'description' => 'جريان الصوت سريعاً عند النطق. الحروف المذلقة: فِرَّ مِنْ لُبٍّ (الفاء-الراء-الميم-النون-اللام-الباء).',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 108,
                'example_verse_key' => '2:36',
                'examples' => ['فَتَلَقَّى آدَمُ مِن رَّبِّهِ كَلِمَاتٍ'],
            ],
            [
                'name' => 'صفة الإصمات',
                'description' => 'عدم جريان الصوت. باقي الحروف غير المذلقة.',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 109,
                'example_verse_key' => '2:30',
                'examples' => ['وَإِذْ قَالَ رَبُّكَ لِلْمَلَائِكَةِ'],
            ],
            [
                'name' => 'صفة اللين',
                'description' => 'سهولة النطق بالحرف. حروف اللين: الواو الساكنة المضمومة ما قبلها، والياء الساكنة المكسورة ما قبلها.',
                'category' => TajweedRuleDefinition::CATEGORY_SIFAT,
                'order' => 110,
                'example_verse_key' => '2:11',
                'examples' => ['قَالُوا لَن نَّدْخُلَهَا أَبَدًا'],
            ],

            // ============================
            // النون الساكنة والتنوين
            // ============================
            [
                'name' => 'الإظهار الحلقي',
                'description' => 'إظهار النون الساكنة أو التنوين نطقاً من غير غنة عند أحد حروف الحلق الستة: الهمزة، الهاء، العين، الحاء، الغين، الخاء.',
                'category' => TajweedRuleDefinition::CATEGORY_NOON_SAKIN,
                'order' => 200,
                'example_verse_key' => '1:5',
                'examples' => ['إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ'],
            ],
            [
                'name' => 'الإدغام بغنة',
                'description' => 'إدخال النون الساكنة أو التنوين في الحرف الذي يليها بحيث تصيران حرفاً مشدداً، مع بقاء الغنة. حروفه: ينْمُو (النون-الميم-الواو-الياء).',
                'category' => TajweedRuleDefinition::CATEGORY_NOON_SAKIN,
                'order' => 201,
                'example_verse_key' => '2:8',
                'examples' => ['مِنَ النَّاسِ مَن يَقُولُ آمَنَّا بِاللَّهِ'],
            ],
            [
                'name' => 'الإدغام بلا غنة',
                'description' => 'إدخال النون الساكنة أو التنوين في اللام أو الراء بحيث تُضغم ولا تبقى غنة.',
                'category' => TajweedRuleDefinition::CATEGORY_NOON_SAKIN,
                'order' => 202,
                'example_verse_key' => '2:10',
                'examples' => ['قُلْ هَلْ نُنَبِّئُكُمْ بِالْأَخْسَرِينَ أَعْمَالًا'],
            ],
            [
                'name' => 'الإقلاب',
                'description' => 'قلب النون الساكنة أو التنوين ميماً مخفاة عند الباء، مع إبقاء الغنة.',
                'category' => TajweedRuleDefinition::CATEGORY_NOON_SAKIN,
                'order' => 203,
                'example_verse_key' => '2:8',
                'examples' => ['يُخَادِعُونَ اللَّهَ وَالَّذِينَ آمَنُوا'],
            ],
            [
                'name' => 'الإخفاء الحقيقي',
                'description' => 'إخفاء النون الساكنة أو التنوين بحيث يبقى بين الإظهار والإدغام، مع بقاء الغنة. حروفه: صِفْ ذَا ثَنَا كَدَّ جَدٍّ شَطْ (15 حرفاً).',
                'category' => TajweedRuleDefinition::CATEGORY_NOON_SAKIN,
                'order' => 204,
                'example_verse_key' => '2:6',
                'examples' => ['إِنَّ الَّذِينَ كَفَرُوا سَوَاءٌ عَلَيْهِمْ'],
            ],

            // ============================
            // الميم الساكنة
            // ============================
            [
                'name' => 'الإخفاء الشفوي',
                'description' => 'إخفاء الميم الساكنة عند الباء، مع بقاء الغنة الشفوية.',
                'category' => TajweedRuleDefinition::CATEGORY_MEEM_SAKIN,
                'order' => 300,
                'example_verse_key' => '2:14',
                'examples' => ['وَإِذَا لَقُوا الَّذِينَ آمَنُوا قَالُوا آمَنَّا'],
            ],
            [
                'name' => 'الإدغام الشفوي المتماثل',
                'description' => 'إدغام الميم الساكنة في الميم المتحركة التي تليها. تصبح ميماً مشددة.',
                'category' => TajweedRuleDefinition::CATEGORY_MEEM_SAKIN,
                'order' => 301,
                'example_verse_key' => '2:7',
                'examples' => ['خَتَمَ اللَّهُ عَلَىٰ قُلُوبِهِمْ وَعَلَىٰ سَمْعِهِمْ'],
            ],
            [
                'name' => 'الإظهار الشفوي',
                'description' => 'إظهار الميم الساكنة عند باقي الحروف (غير الباء والميم).',
                'category' => TajweedRuleDefinition::CATEGORY_MEEM_SAKIN,
                'order' => 302,
                'example_verse_key' => '2:23',
                'examples' => ['وَإِن كُنتُمْ فِي رَيْبٍ مِّمَّا نَزَّلْنَا عَلَىٰ عَبْدِنَا'],
            ],

            // ============================
            // المد
            // ============================
            [
                'name' => 'المد الطبيعي',
                'description' => 'مد الحرف بحرف مد (الألف أو الواو أو الياء) بدون سبب (لا ساكن قبل ولا سكون لازم). مقداره حركتان.',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 400,
                'example_verse_key' => '1:1',
                'examples' => ['الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'المد المتصل',
                'description' => 'إذا وقع بعد حرف المد حرف ساكن متحرك (أي متحرك ثم ساكن) في كلمة واحدة. مقداره أربع أو خمس حركات (أو أربع ونصف).',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 401,
                'example_verse_key' => '2:2',
                'examples' => ['ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ'],
            ],
            [
                'name' => 'المد المنفصل',
                'description' => 'إذا وقع حرف المد في آخر الكمة والساكن في أول الكلمة التالية. مقداره أربع أو خمس حركات (أو أربع ونصف).',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 402,
                'example_verse_key' => '36:52',
                'examples' => ['قَالُوا يَا وَيْلَنَا مَن بَعَثَنَا مِن مَّرْقَدِنَا'],
            ],
            [
                'name' => 'مد البدل',
                'description' => 'إذا وقع حرف مد بعد همزة في نفس الكلمة. مقداره حركتان (أو أربع عند الإشمام).',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 403,
                'example_verse_key' => '2:75',
                'examples' => ['أَتُؤْمِنُونَ بِبَعْضِ الْكِتَابِ وَتَكْفُرُونَ بِبَعْضٍ'],
            ],
            [
                'name' => 'المد اللازم',
                'description' => 'الحرف المشدد الذي يأتي بعد حرف مد (المد اللازم الحرفيّ). أو المد الذي يكون سببه حرف ساكن أصليّ ثابت (المد اللازم الكلميّ). مقداره ست حركات.',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 404,
                'example_verse_key' => '2:10',
                'examples' => ['قُلْ هَلْ نُنَبِّئُكُمْ بِالْأَخْسَرِينَ أَعْمَالًا'],
            ],
            [
                'name' => 'المد العارض للسكون',
                'description' => 'إذا وقف القارئ على كلمة آخرها حرف مد ووقف بسكون. مقداره حركتان أو أربع أو ست.',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 405,
                'example_verse_key' => '1:4',
                'examples' => ['مَالِكِ يَوْمِ الدِّينِ'],
            ],
            [
                'name' => 'مد الصلة الصغرى',
                'description' => 'المد الذي يقع بين همزة الوصل وحرف المد في بداية الكلمة. مقداره حركتان.',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 406,
                'example_verse_key' => '2:1',
                'examples' => ['الم (ذَٰلِكَ الْكِتَابُ)'],
            ],
            [
                'name' => 'مد الصلة الكبرى',
                'description' => 'إذا وقعت همزة وصل بين همزة الاستفهام وحرف المد. مقداره أربع أو خمس أو ست حركات.',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 407,
                'example_verse_key' => '2:217',
                'examples' => ['يَسْأَلُونَكَ عَنِ الشَّهْرِ الْحَرَامِ'],
            ],

            // ============================
            // اللام
            // ============================
            [
                'name' => 'اللام الشمسية',
                'description' => 'اللام التي تُكتب ولا تُنطق. تسبق حروف الشمسية (ت ث د ذ ر ز س ش ص ض ط ظ ل ن). الحروف الشمسية: طِبْ ثُمَّ صِلْ رُحْمًا تَفُزْ ضِفْ ذَا نُجْحٍ شَرَفًا لَكَ (14 حرفاً).',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 500,
                'example_verse_key' => '1:1',
                'examples' => ['الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'اللام القمرية',
                'description' => 'اللام التي تُكتب وتُنطق. تسبق باقي الحروف (غير الشمسية).',
                'category' => TajweedRuleDefinition::CATEGORY_MADD,
                'order' => 501,
                'example_verse_key' => '2:1',
                'examples' => ['الْبَقَرَةِ'],
            ],

            // ============================
            // القلقلة
            // ============================
            [
                'name' => 'القلقلة',
                'description' => 'نطق الحرف بحركة قوية مضطرمة عند الوقف عليه أو عند السكون. حروف القلقلة: ق ط ب ج د (جمْعُ قَطٍّ بَدَدْ). يكون عند الوقف أشدّ.',
                'category' => TajweedRuleDefinition::CATEGORY_QALQALA,
                'order' => 600,
                'example_verse_key' => '2:74',
                'examples' => ['أَوْ يَأْتِيَهُمْ عَذَابٌ غَيْرُ مُهْتَدٍ'],
            ],

            // ============================
            // الوقف والابتداء
            // ============================
            [
                'name' => 'الوقف المطلق',
                'description' => 'الوقف على آخر الكلمة بحيث يكون المعنى تاماً ولا يتوقف على أحد معانٍ المتشابهات.',
                'category' => TajweedRuleDefinition::CATEGORY_WAQF,
                'order' => 700,
                'example_verse_key' => '2:1',
                'examples' => ['الم (ذَٰلِكَ الْكِتَابُ)'],
            ],
            [
                'name' => 'الوقف الممنوع',
                'description' => 'الوقف الذي يُمنع لفساد المعنى أو لارتباط الكلام بعضه ببعض.',
                'category' => TajweedRuleDefinition::CATEGORY_WAQF,
                'order' => 701,
                'example_verse_key' => '2:2',
                'examples' => ['ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ'],
            ],
            [
                'name' => 'الوقف الجائز',
                'description' => 'الوقف الذي يجوز الوقف عليه والوصل بعده.',
                'category' => TajweedRuleDefinition::CATEGORY_WAQF,
                'order' => 702,
                'example_verse_key' => '2:3',
                'examples' => ['الَّذِينَ يُؤْمِنُونَ بِالْغَيْبِ وَيُقِيمُونَ الصَّلَاةَ'],
            ],
            [
                'name' => 'السكت',
                'description' => 'قطع الصوت بدون نفس بين حرفين. مواضعه: بين حرفين متشابهين في المخرج (كـ"إِنَّ الَّذِينَ كَفَرُوا") أو بين حرفين متقاربين.',
                'category' => TajweedRuleDefinition::CATEGORY_WAQF,
                'order' => 703,
                'example_verse_key' => '2:6',
                'examples' => ['إِنَّ الَّذِينَ كَفَرُوا سَوَاءٌ عَلَيْهِمْ'],
            ],

            // ============================
            // الغنة
            // ============================
            [
                'name' => 'الغنة',
                'description' => 'إخراج الصوت من الخيشوم (الأنف). تظهر في: النون الساكنة والتنوين عند الإخفاء والإدغام والإقلاب، والميم الساكنة عند الإخفاء والإدغام.',
                'category' => TajweedRuleDefinition::CATEGORY_GHUNNA,
                'order' => 800,
                'example_verse_key' => '1:1',
                'examples' => ['بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ'],
            ],
            [
                'name' => 'أقسام الغنة',
                'description' => 'الغنة تُقسم إلى: غنة كاملة (في الإدغام بغنة)، وغنة ناقصة (في الإخفاء)، وغنة شفوية (في الميم الساكنة).',
                'category' => TajweedRuleDefinition::CATEGORY_GHUNNA,
                'order' => 801,
                'example_verse_key' => '2:8',
                'examples' => ['مِنَ النَّاسِ مَن يَقُولُ آمَنَّا بِاللَّهِ'],
            ],

            // ============================
            // الإدغام العام
            // ============================
            [
                'name' => 'الإدغام المتماثل',
                'description' => 'إدغام حرف في مثله (متماثلين في المخرج والصفة)، مثل الميم الساكنة في الميم.',
                'category' => TajweedRuleDefinition::CATEGORY_IDGHAM,
                'order' => 900,
                'example_verse_key' => '2:7',
                'examples' => ['خَتَمَ اللَّهُ عَلَىٰ قُلُوبِهِمْ وَعَلَىٰ سَمْعِهِمْ'],
            ],
            [
                'name' => 'الإدغام المتقارب',
                'description' => 'إدغام حرف في متقاربه (متقاربان في المخرج أو في الصفة)، مثل اللام في الراء أو الراء في اللام.',
                'category' => TajweedRuleDefinition::CATEGORY_IDGHAM,
                'order' => 901,
                'example_verse_key' => '2:14',
                'examples' => ['وَإِذَا لَقُوا الَّذِينَ آمَنُوا قَالُوا آمَنَّا'],
            ],
            [
                'name' => 'الإدغام المتجانس',
                'description' => 'إدغام حرف في متجانسه (متشابهان في الصفة ومتقاربان في المخرج).',
                'category' => TajweedRuleDefinition::CATEGORY_IDGHAM,
                'order' => 902,
                'example_verse_key' => '2:142',
                'examples' => ['سَيَقُولُ السُّفَهَاءُ مِنَ النَّاسِ'],
            ],
        ];

        foreach ($rules as $rule) {
            TajweedRuleDefinition::create($rule);
        }

        $this->command->info('✅ تم إدخال ' . count($rules) . ' قاعدة تجويد بنجاح!');
    }
}
