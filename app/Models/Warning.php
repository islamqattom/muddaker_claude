<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'issued_by', 'warning_number', 'type', 'title',
        'content', 'issue_date', 'issue_day', 'from_date', 'from_day', 'to_date', 'to_day',
        'absent_days', 'late_days', 'total_days',
        'failed_sessions', 'total_sessions',
        'additional_notes', 'is_read_by_parent',
        'attendance_data', 'recitation_data', 'test_data'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'is_read_by_parent' => 'boolean',
        'attendance_data' => 'array',
        'recitation_data' => 'array',
        'test_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->issue_date && $model->issue_date !== '') {
                $model->issue_day = self::getArabicDay($model->issue_date);
            }
            if ($model->from_date && $model->from_date !== '') {
                $model->from_day = self::getArabicDay($model->from_date);
            }
            if ($model->to_date && $model->to_date !== '') {
                $model->to_day = self::getArabicDay($model->to_date);
            }
        });
    }

    /**
     * Get Arabic day name from date
     */
    public static function getArabicDay($date): string
    {
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $dateObj = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $days[$dateObj->dayOfWeek];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'attendance' => 'إنذار حضور',
            'recitation' => 'إنذار تسميع',
            'general' => 'إنذار عام',
            default => $this->type,
        };
    }

    public static function generateWarningNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'WARN-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
