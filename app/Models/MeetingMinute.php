<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_number',
        'meeting_date',
        'meeting_time',
        'location',
        'attendees',
        'absent',
        'agenda',
        'decisions',
        'recommendations',
        'next_meeting_date',
        'created_by',
        'status',
        'manual_attendees',
        'program_axes',
        'program_axes_names',
        'agenda_notes',
        'assignments',
        'excluded_supervisors',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'next_meeting_date' => 'date',
        'attendees' => 'array',
        'absent' => 'array',
        'agenda' => 'array',
        'agenda_notes' => 'array',
        'program_axes' => 'array',
        'program_axes_names' => 'array',
        'manual_attendees' => 'array',
        'assignments' => 'array',
        'excluded_supervisors' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MeetingTask::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'approved' => 'success',
            'archived' => 'warning',
            default => 'secondary'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'مسودة',
            'approved' => 'معتمد',
            'archived' => 'مؤرشف',
            default => 'غير معروف'
        };
    }
}
