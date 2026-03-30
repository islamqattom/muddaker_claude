<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id', 'name', 'juz_number', 'target_pages',
        'start_date', 'end_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function participants()
    {
        return $this->hasMany(GroupEventParticipant::class);
    }

    public function progress()
    {
        return $this->hasMany(GroupEventProgress::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'مسودة',
            'active' => 'نشط',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
            default => $this->status,
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isParticipating(Student $student): bool
    {
        return $this->participants()->where('student_id', $student->id)->exists();
    }
}
