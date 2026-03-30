<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupEventProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'date', 'total_pages', 'participating_students', 'completion_percentage',
    ];

    protected $casts = [
        'date' => 'date',
        'completion_percentage' => 'float',
    ];

    public function event()
    {
        return $this->belongsTo(GroupEvent::class);
    }
}
