<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Show the notification settings page.
     * Includes the user's per-type preferences, the list of subscribed
     * devices, and a test button.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $types = NotificationPreference::defaultTypes();

        $preferences = $user->notificationPreferences()
            ->get()
            ->keyBy('type');

        $devices = $user->pushSubscriptions()
            ->orderByDesc('last_used_at')
            ->get();

        return view('notifications.settings', [
            'types' => $types,
            'preferences' => $preferences,
            'devices' => $devices,
            'hasActivePush' => $user->hasActivePush(),
        ]);
    }

    /**
     * Update per-type notification preferences. Accepts an array of
     * { type: { enabled: bool, push_enabled: bool } } entries.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.type' => 'required|string|max:50',
            'preferences.*.enabled' => 'required|boolean',
            'preferences.*.push_enabled' => 'required|boolean',
        ]);

        $user = $request->user();
        $validTypes = array_keys(NotificationPreference::defaultTypes());

        foreach ($validated['preferences'] as $entry) {
            if (!in_array($entry['type'], $validTypes, true)) {
                continue;
            }
            NotificationPreference::updateOrCreate(
                ['user_id' => $user->id, 'type' => $entry['type']],
                [
                    'enabled' => (bool) $entry['enabled'],
                    'push_enabled' => (bool) $entry['push_enabled'],
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Send a test push notification to the current user's devices.
     * Used by the "Test notification" button in the settings page so the
     * user can verify their devices are working.
     */
    public function test(Request $request)
    {
        $user = $request->user();

        if (!$user->hasActivePush()) {
            return response()->json([
                'success' => false,
                'error' => 'لا توجد أجهزة مفعلة لاستقبال الإشعارات. فعّل الإشعارات من المتصفح أولاً.',
            ], 400);
        }

        try {
            $this->notificationService->sendToUser(
                $user,
                'test',
                'إشعار اختباري',
                'هذا إشعار اختباري للتأكد من عمل الإشعارات على هذا الجهاز.',
                route('notifications.settings'),
                null,
                null,
                ['message_preview' => 'إذا ظهر هذا الإشعار فالإعدادات تعمل بشكل صحيح ✅']
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار الاختباري. تحقق من جهازك.',
            ]);
        } catch (\Exception $e) {
            Log::error('Test notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'فشل إرسال الإشعار الاختباري',
            ], 500);
        }
    }
}
