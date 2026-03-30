<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupEventParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'student_id', 'pages_memorized', 'last_page_reached', 'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(GroupEvent::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
