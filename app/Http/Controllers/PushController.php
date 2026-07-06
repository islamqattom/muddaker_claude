<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushController extends Controller
{
    /**
     * Register (or refresh) a push subscription for the authenticated user.
     * A user may have multiple subscriptions (phone, desktop, tablet) — each
     * device's subscription is keyed by its unique endpoint URL.
     */
    public function subscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:2048',
            'key_p256dh' => 'nullable|string|max:512',
            'key_auth' => 'nullable|string|max:256',
        ]);

        try {
            $subscription = PushSubscription::updateOrCreate(
                ['endpoint' => $validated['endpoint']],
                [
                    'user_id' => $request->user()->id,
                    'key_p256dh' => $validated['key_p256dh'],
                    'key_auth' => $validated['key_auth'],
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                    'device_name' => substr((string) $request->input('device_name'), 0, 100) ?: null,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'device' => $subscription->deviceLabel(),
            ]);
        } catch (\Exception $e) {
            Log::error('Push subscription failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to register push subscription',
            ], 500);
        }
    }

    /**
     * Remove a push subscription. Accepts either an endpoint (legacy) or a
     * subscription id (preferred, more secure — endpoint URLs can be long
     * and identifying the user from the body alone is fragile).
     */
    public function unsubscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'nullable|string|max:2048',
            'subscription_id' => 'nullable|integer',
        ]);

        try {
            $query = PushSubscription::where('user_id', $request->user()->id);

            if (!empty($validated['subscription_id'])) {
                $query->where('id', $validated['subscription_id']);
            } elseif (!empty($validated['endpoint'])) {
                $query->where('endpoint', $validated['endpoint']);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'subscription_id or endpoint is required',
                ], 422);
            }

            $deleted = $query->delete();

            return response()->json(['success' => true, 'deleted' => $deleted]);
        } catch (\Exception $e) {
            Log::error('Push unsubscription failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove push subscription',
            ], 500);
        }
    }

    /**
     * Return the current user's subscription list (active + inactive) and
     * browser push permission status (read from the request, since permission
     * is a browser concept). Used by the settings page to show "where am I
     * signed in".
     */
    public function status(Request $request): \Illuminate\Http\JsonResponse
    {
        $subscriptions = PushSubscription::where('user_id', $request->user()->id)
            ->orderByDesc('last_used_at')
            ->get(['id', 'user_agent', 'device_name', 'last_used_at', 'is_active', 'created_at'])
            ->map(function (PushSubscription $sub) {
                return [
                    'id' => $sub->id,
                    'device' => $sub->deviceLabel(),
                    'last_used_at' => $sub->last_used_at?->toIso8601String(),
                    'is_active' => $sub->is_active,
                    'created_at' => $sub->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
        ]);
    }
}
