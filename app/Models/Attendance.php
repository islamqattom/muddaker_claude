<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use App\Traits\HasArabicDay;

class Attendance extends Model
{
    use HasFactory, Auditable, HasArabicDay;

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
            'holiday' => 'عطلة',
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
            'holiday' => 'purple',
            default => 'secondary',
        };
    }
}
