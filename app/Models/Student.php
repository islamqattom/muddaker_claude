<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'supervisor_id', 'level_id', 'student_number',
        'national_id', 'birth_date', 'phone', 'address',
        'memorized_parts', 'current_part', 'current_page',
        'enrollment_date', 'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function parents()
    {
        return $this->belongsToMany(ParentProfile::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function recitationSessions()
    {
        return $this->hasMany(RecitationSession::class);
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getAttendanceRateAttribute(): float
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0;
        $present = $this->attendances()->whereIn('status', ['present', 'late'])->count();
        return round(($present / $total) * 100, 1);
    }

    public function getTodayAttendanceAttribute()
    {
        return $this->attendances()->where('date', today())->first();
    }

    public function getPassRateAttribute(): float
    {
        $total = $this->recitationSessions()->whereIn('status', ['passed', 'failed'])->count();
        if ($total === 0) return 0;
        $passed = $this->recitationSessions()->where('status', 'passed')->count();
        return round(($passed / $total) * 100, 1);
    }
}
