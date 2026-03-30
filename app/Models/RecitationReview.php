<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecitationReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_item_id', 'student_id', 'quality', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewItem()
    {
        return $this->belongsTo(ReviewItem::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
