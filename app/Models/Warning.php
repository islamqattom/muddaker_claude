<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'issued_by', 'warning_number', 'type', 'title',
        'content', 'issue_date', 'from_date', 'to_date',
        'absent_days', 'late_days', 'total_days',
        'failed_sessions', 'total_sessions',
        'additional_notes', 'is_read_by_parent'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'is_read_by_parent' => 'boolean',
    ];

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
