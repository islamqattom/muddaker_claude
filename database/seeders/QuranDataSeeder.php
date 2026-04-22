<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Surah;
use App\Models\Ayah;

class QuranDataSeeder extends Seeder
{
    private const QURAN_API_URL = 'https://api.alquran.cloud/v1/quran';
    private const JUZ_BOUNDARIES = [1, 22, 42, 62, 82, 102, 122, 142, 162, 182, 202, 222, 242, 262, 282, 302, 322, 342, 362, 382, 402, 422, 442, 462, 482, 502, 522, 542, 562, 582, 602, 6236];

    public function run(): void
    {
        $this->command->info('جاري جلب بيانات القرآن الكريم...');

        try {
            $response = Http::timeout(60)->get(self::QURAN_API_URL);

            if ($response->failed()) {
                $this->command->error('فشل في جلب البيانات من API');
                return;
            }

            $data = $response->json();

            if (!isset($data['data']['surahs'])) {
                $this->command->error('تنسيق البيانات غير صحيح');
                return;
            }

            $surahs = $data['data']['surahs'];

            foreach ($surahs as $surahData) {
                $surah = Surah::updateOrInsert(
                    ['surah_number' => $surahData['number']],
                    [
                        'name_ar' => $surahData['name'],
                        'name_en' => $surahData['englishName'] ?? null,
                        'total_ayahs' => count($surahData['ayahs']),
                    ]
                );

                $surah = Surah::firstWhere('surah_number', $surahData['number']);

                foreach ($surahData['ayahs'] as $ayahData) {
                    Ayah::updateOrInsert(
                        [
                            'surah_id' => $surah->id,
                            'ayah_number' => $ayahData['numberInSurah'],
                        ],
                        [
                            'text' => $ayahData['text'],
                            'page_number' => $ayahData['page'] ?? $this->calculatePageNumber($ayahData['number']),
                            'juz_number' => $ayahData['juz'] ?? $this->calculateJuzNumber($ayahData['number']),
                        ]
                    );
                }

                $this->command->info("تم استيراد السورة: {$surahData['name']}");
            }

            $this->command->info('تم استيراد جميع بيانات القرآن بنجاح!');

        } catch (\Exception $e) {
            $this->command->error('حدث خطأ: ' . $e->getMessage());
        }
    }

    private function calculatePageNumber(int $ayahNumber): int
    {
        return (int) ceil($ayahNumber / 10.4);
    }

    private function calculateJuzNumber(int $ayahNumber): int
    {
        for ($i = 1; $i < count(self::JUZ_BOUNDARIES); $i++) {
            if ($ayahNumber < self::JUZ_BOUNDARIES[$i]) {
                return $i;
            }
        }

        return 30;
    }
}
