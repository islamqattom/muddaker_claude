<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecitationPage extends Model
{
    protected $table = 'recitation_pages';

    protected $fillable = [
        'session_id', 'page_id', 'page_number', 'errors',
        'hesitations', 'color_status', 'is_passed', 'page_score'
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'page_score' => 'float',
    ];

    public function session()
    {
        return $this->belongsTo(RecitationSession::class, 'session_id');
    }

    public function getColorLabelAttribute(): string
    {
        return match($this->color_status) {
            'green' => 'ممتاز',
            'orange' => 'جيد',
            'red' => 'ضعيف',
            'black' => 'راسب',
            default => 'جارٍ',
        };
    }

    public function getColorClassAttribute(): string
    {
        return match($this->color_status) {
            'green' => 'bg-success text-white',
            'orange' => 'bg-warning text-dark',
            'red' => 'bg-danger text-white',
            'black' => 'bg-dark text-white',
            default => 'bg-light',
        };
    }
}
