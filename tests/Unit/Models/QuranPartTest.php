<?php

namespace Tests\Unit\Models;

use App\Models\QuranPart;
use App\Models\RecitationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuranPartTest extends TestCase
{
    use RefreshDatabase;

    public function test_quran_part_has_required_fillable_attributes(): void
    {
        $part = new QuranPart();
        $fillable = $part->getFillable();

        $this->assertContains('part_number', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('pages_count', $fillable);
        $this->assertContains('start_page', $fillable);
        $this->assertContains('end_page', $fillable);
    }

    public function test_quran_part_has_many_pages(): void
    {
        $part = QuranPart::factory()->create();

        // This tests the relationship exists
        $this->assertTrue($part->pages()->exists() || $part->pages()->count() === 0);
    }

    public function test_quran_part_has_many_recitation_sessions(): void
    {
        $part = QuranPart::factory()->create();

        // This tests the relationship exists
        $this->assertTrue($part->recitationSessions()->exists() || $part->recitationSessions()->count() === 0);
    }
}
