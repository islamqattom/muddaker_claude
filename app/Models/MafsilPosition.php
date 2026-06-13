<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MafsilPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'juz_number',
        'position_number',
        'list_order',
        'start_ayah_id',
        'end_ayah_id',
        'difficulty',
        'notes',
    ];

    protected $casts = [
        'juz_number' => 'integer',
        'position_number' => 'integer',
        'list_order' => 'integer',
        'difficulty' => 'integer',
    ];

    public function startAyah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class, 'start_ayah_id');
    }

    public function endAyah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class, 'end_ayah_id');
    }

    public function difficultyLabel(): string
    {
        return match ($this->difficulty) {
            1 => 'سهل',
            2 => 'متوسط',
            3 => 'صعب',
            default => 'متوسط',
        };
    }

    public function positionRangeLabel(): string
    {
        $start = $this->startAyah;
        $end = $this->endAyah;

        if (! $start || ! $end) {
            return '';
        }

        $startSurah = $start->surah->name_ar ?? '';
        $endSurah = $end->surah->name_ar ?? '';

        if ($start->surah_id === $end->surah_id) {
            return "{$startSurah} {$start->ayah_number} - {$end->ayah_number}";
        }

        return "{$startSurah} {$start->ayah_number} - {$endSurah} {$end->ayah_number}";
    }

    public function pageNumberLabel(): ?string
    {
        $start = $this->startAyah;

        return $start ? (string) $start->page_number : null;
    }
}
