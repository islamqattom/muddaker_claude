<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'supervisor_id', 'date', 'day', 'status',
        'arrival_time', 'scheduled_time', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->date && $model->date !== '') {
                $model->day = self::getArabicDay($model->date);
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

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'غائب بعذر',
            default => 'غير محدد',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'excused' => 'info',
            default => 'secondary',
        };
    }
}
