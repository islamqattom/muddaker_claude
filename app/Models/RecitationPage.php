<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecitationPage extends Model
{
    protected $table = 'recitation_pages';

    protected $fillable = [
        'session_id', 'page_id', 'mafsil_position_id', 'page_number', 'position', 'position_range',
        'page_number_in_mushaf', 'errors', 'hesitations', 'color_status', 'is_passed', 'page_score'
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'page_score' => 'float',
        'page_number_in_mushaf' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(RecitationSession::class, 'session_id');
    }

    public function page()
    {
        return $this->belongsTo(QuranPage::class, 'page_id');
    }

    public function mafsilPosition()
    {
        return $this->belongsTo(MafsilPosition::class, 'mafsil_position_id');
    }

    public function tajweedErrors()
    {
        return $this->hasMany(RecitationTajweedError::class, 'recitation_page_id');
    }

    public function getPositionLabelAttribute(): ?string
    {
        if ($this->mafsil_position_id && $this->mafsilPosition) {
            return $this->mafsilPosition->difficultyLabel() . ' - ' . $this->mafsilPosition->positionRangeLabel();
        }

        return match($this->position) {
            1 => 'الموضع الأول',
            2 => 'الموضع الثاني',
            3 => 'الموضع الثالث',
            default => null,
        };
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
