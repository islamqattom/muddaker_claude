<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushController extends Controller
{
    public function subscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'key_p256dh' => 'nullable|string',
            'key_auth' => 'nullable|string',
        ]);

        try {
            PushSubscription::updateOrCreate(
                ['endpoint' => $request->input('endpoint')],
                [
                    'user_id' => $request->user()->id,
                    'key_p256dh' => $request->input('key_p256dh'),
                    'key_auth' => $request->input('key_auth'),
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Push subscription failed', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            return response()->json(['success' => false, 'error' => 'Failed to subscribe to push notifications'], 500);
        }
    }

    public function unsubscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        try {
            PushSubscription::where('endpoint', $request->input('endpoint'))
                ->where('user_id', $request->user()->id)
                ->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Push unsubscription failed', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            return response()->json(['success' => false, 'error' => 'Failed to unsubscribe from push notifications'], 500);
        }
    }
}
