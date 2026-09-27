<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorAlert extends Model
{
    public const TYPE_PART_MEMORIZED = 'part_memorized';
    public const TYPE_MAFSIL_REQUIRED = 'mafsil_required';

    public const MAFSIL_STATIONS = [3, 6, 10, 15, 20, 25, 30];

    protected $fillable = [
        'student_id',
        'supervisor_id',
        'type',
        'reference',
        'part_number',
        'station',
        'recitation_session_id',
        'read_at',
    ];

    protected $casts = [
        'part_number' => 'integer',
        'station' => 'integer',
        'read_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function recitationSession(): BelongsTo
    {
        return $this->belongsTo(RecitationSession::class, 'recitation_session_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function partReference(int $partNumber): string
    {
        return 'part_' . $partNumber;
    }

    public static function stationReference(int $station): string
    {
        return 'station_' . $station;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PART_MEMORIZED => 'إتمام حفظ جزء',
            self::TYPE_MAFSIL_REQUIRED => 'مطلوب مفصل',
            default => $this->type,
        };
    }
}
