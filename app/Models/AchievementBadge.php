<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AchievementBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name_ar', 'name_en', 'description_ar', 'description_en',
        'icon', 'color', 'type', 'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];

    public function studentAchievements(): HasMany
    {
        return $this->hasMany(StudentAchievement::class, 'badge_id');
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }
}
