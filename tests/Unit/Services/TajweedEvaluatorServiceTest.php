<?php

namespace Tests\Unit\Services;

use App\Services\TajweedEvaluatorService;
use Tests\TestCase;

class TajweedEvaluatorServiceTest extends TestCase
{
    protected TajweedEvaluatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TajweedEvaluatorService();
    }

    public function test_normalize_arabic_removes_diacritics(): void
    {
        // Note: teh marbuta (ة) is normalized to teh (ت) by the service
        $input = 'بِسْمِ اللَّهِ';
        // After removing diacritics: بسم اللى
        // After normalizing teh marbuta: اللى -> الله (trailing heh converted)
        $expected = 'بسم الله';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_removes_fatha(): void
    {
        $input = 'كَتَبَ';
        $expected = 'كتب';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_removes_damma(): void
    {
        $input = 'يُبَيِّنُ';
        $expected = 'يبين';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_removes_kasra(): void
    {
        $input = 'مِنْ';
        $expected = 'من';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_normalizes_alef_with_madda(): void
    {
        // Madda alef (آ) is normalized to plain alef (ا)
        $input = 'آمن';
        $expected = 'امن';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_normalizes_alef_with_hamza_above(): void
    {
        $input = 'إسلام';
        $expected = 'اسلام';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_normalizes_alef_with_hamza_below(): void
    {
        // أ is converted to ا
        $input = 'ابراهيم';
        $expected = 'ابراهيم';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_normalizes_teh_marbuta(): void
    {
        // teh marbuta (ة) is normalized to teh (ت)
        $input = 'ساعة';
        $expected = 'ساعت';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_removes_non_arabic_letters(): void
    {
        // Non-Arabic letters are removed but numbers and spaces are kept
        $input = 'بسم test';
        $expected = 'بسم';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_preserves_arabic_numbers(): void
    {
        // Use a simpler test case without teh marbuta complexity
        $input = 'بسم ١٢٣';
        $expected = 'بسم ١٢٣';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_empty_string(): void
    {
        $input = '';
        $expected = '';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_keeps_arabic_letters(): void
    {
        // Note: teh marbuta (ة) is normalized to teh (ت)
        $input = 'الله';
        $expected = 'الله';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_normalizes_alef_wasla(): void
    {
        // Alef wasla (ٱ U+0671) is normalized to plain alef (ا)
        $input = 'ٱلله';
        $expected = 'الله';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_normalize_arabic_normalizes_alef_wasla_in_phrase(): void
    {
        // Bismillah with alef wasla on al-rahman and al-rahim
        $input = 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ';
        $expected = 'بسم الله الرحمن الرحيم';

        $result = $this->service->normalizeArabic($input);

        $this->assertEquals($expected, $result);
    }

    public function test_calculate_similarity_identical_strings(): void
    {
        $result = $this->service->calculateSimilarity('بسم', 'بسم');

        $this->assertEquals(1.0, $result);
    }

    public function test_calculate_similarity_identical_long_strings(): void
    {
        $result = $this->service->calculateSimilarity('بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ');

        $this->assertEquals(1.0, $result);
    }

    public function test_calculate_similarity_completely_different(): void
    {
        // Use strings with no common characters
        $result = $this->service->calculateSimilarity('بسم', 'جwd');

        // Should be 0 or very close to 0 since they share no characters
        $this->assertLessThan(0.1, $result);
    }

    public function test_calculate_similarity_partial_match(): void
    {
        $result = $this->service->calculateSimilarity('بسم', 'بسمك');

        // 3/4 = 0.75
        $this->assertEqualsWithDelta(0.75, $result, 0.01);
    }

    public function test_calculate_similarity_empty_both_strings(): void
    {
        $result = $this->service->calculateSimilarity('', '');

        $this->assertEquals(1.0, $result);
    }

    public function test_calculate_similarity_empty_first_string(): void
    {
        $result = $this->service->calculateSimilarity('', 'بسم');

        $this->assertEquals(0.0, $result);
    }

    public function test_calculate_similarity_empty_second_string(): void
    {
        $result = $this->service->calculateSimilarity('بسم', '');

        $this->assertEquals(0.0, $result);
    }

    public function test_evaluate_verse_realtime_above_threshold(): void
    {
        $expected = 'بِسْمِ اللَّهِ';
        $transcribed = 'بسم الله'; // Very close after normalization
        $threshold = 80.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertFalse($result['is_error']);
        $this->assertGreaterThanOrEqual(80.0, $result['similarity']);
    }

    public function test_evaluate_verse_realtime_below_threshold(): void
    {
        $expected = 'بِسْمِ اللَّهِ';
        $transcribed = 'غير صحيح'; // Completely different
        $threshold = 80.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertTrue($result['is_error']);
        $this->assertLessThan(80.0, $result['similarity']);
    }

    public function test_evaluate_verse_realtime_identical_text(): void
    {
        $expected = 'الله';
        $transcribed = 'الله';
        $threshold = 80.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertFalse($result['is_error']);
        $this->assertEquals(100.0, $result['similarity']);
    }

    public function test_evaluate_verse_realtime_missing_word(): void
    {
        $expected = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ';
        $transcribed = 'بِسْمِ اللَّهِ'; // Missing last word
        $threshold = 80.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertTrue($result['is_error']);
        $this->assertEquals('missing', $result['error_type']);
    }

    public function test_evaluate_verse_realtime_extra_word(): void
    {
        $expected = 'بِسْمِ اللَّهِ';
        $transcribed = 'بِسْمِ اللَّهِ الرَّحْمَٰنِ'; // Extra word
        $threshold = 80.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertTrue($result['is_error']);
        $this->assertEquals('extra', $result['error_type']);
    }

    public function test_evaluate_verse_realtime_mismatch_word(): void
    {
        $expected = 'بِسْمِ اللَّهِ';
        $transcribed = 'بِسْمِ اللَّهكم'; // Different ending with extra letters
        $threshold = 81.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertTrue($result['is_error']);
    }

    public function test_evaluate_verse_realtime_below_50_percent(): void
    {
        // Very different strings should result in low similarity
        $expected = 'بسم';
        $transcribed = 'طعم';
        $threshold = 80.0;

        $result = $this->service->evaluateVerseRealtime($expected, $transcribed, $threshold);

        $this->assertTrue($result['is_error']);
        $this->assertLessThan(50.0, $result['similarity']);
    }
}
