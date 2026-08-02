<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    public const STATUS_COLOR_MAP = [
        'pending' => '#f39c12',
        'completed' => '#27ae60',
        'cancelled' => '#7f8c8d',
    ];

    public const STATUS_LABEL_MAP = [
        'pending' => 'لم يتم',
        'completed' => 'تم',
        'cancelled' => 'ملغى',
    ];

    protected $fillable = [
        'subject_id',
        'created_by',
        'title',
        'content',
        'lesson_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'sort_order',
        'completed_at',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'completed_at' => 'datetime',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL_MAP[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLOR_MAP[$this->status] ?? '#f39c12';
    }

    public function getDayNameAttribute(): string
    {
        if (!$this->lesson_date) {
            return '-';
        }
        $days = [
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];
        return $days[$this->lesson_date->format('l')] ?? $this->lesson_date->format('l');
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        return $this->lesson_date && $this->lesson_date->isPast();
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->lesson_date && $this->lesson_date->isToday();
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === 'pending' && $this->lesson_date && $this->lesson_date->isFuture();
    }

    public function getTimeRangeAttribute(): string
    {
        if (!$this->start_time) {
            return '-';
        }
        $start = $this->start_time->format('H:i');
        if ($this->end_time) {
            return $start . ' - ' . $this->end_time->format('H:i');
        }
        return $start;
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markPending(): void
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    public function toCalendarEvent(Subject $subject): array
    {
        $hasTime = $this->start_time !== null;
        $dateStr = $this->lesson_date ? $this->lesson_date->format('Y-m-d') : null;

        $start = $hasTime && $dateStr
            ? $dateStr . 'T' . $this->start_time->format('H:i:s')
            : ($dateStr ?: now()->format('Y-m-d'));

        $end = ($hasTime && $this->end_time && $dateStr)
            ? $dateStr . 'T' . $this->end_time->format('H:i:s')
            : null;

        $statusClass = $this->status;
        if ($this->is_overdue) {
            $statusClass = 'overdue';
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $start,
            'end' => $end,
            'allDay' => !$hasTime,
            'color' => $this->status_color,
            'extendedProps' => [
                'subjectId' => $this->subject_id,
                'subjectName' => $subject->name,
                'subjectColor' => $subject->color,
                'title' => $this->title,
                'content' => $this->content,
                'notes' => $this->notes,
                'status' => $this->status,
                'statusLabel' => $this->status_label,
                'statusClass' => $statusClass,
                'lessonDate' => $dateStr,
                'dayName' => $this->day_name,
                'startTime' => $hasTime ? $this->start_time->format('H:i') : null,
                'endTime' => $this->end_time ? $this->end_time->format('H:i') : null,
                'isOverdue' => $this->is_overdue,
                'isToday' => $this->is_today,
            ],
        ];
    }
}
