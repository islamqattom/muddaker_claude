<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'enabled',
        'push_enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Default notification types shown in the settings page.
     * Matches the values used in Notification::type.
     */
    public static function defaultTypes(): array
    {
        return [
            'message' => ['label' => 'الرسائل', 'icon' => 'fa-comment'],
            'warning' => ['label' => 'الإنذارات', 'icon' => 'fa-exclamation-triangle'],
            'warning_draft' => ['label' => 'مسودات الإنذارات', 'icon' => 'fa-edit'],
            'recitation_session' => ['label' => 'جلسات التسميع', 'icon' => 'fa-microphone'],
            'attendance' => ['label' => 'الحضور والغياب', 'icon' => 'fa-calendar-check'],
            'meeting_task' => ['label' => 'مهام الاجتماعات', 'icon' => 'fa-tasks'],
            'milestone' => ['label' => 'الإنجازات', 'icon' => 'fa-trophy'],
        ];
    }
}
