<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications()->orderBy('created_at', 'desc');

        if ($request->filled('filter')) {
            $query->where('type', $request->filter);
        }

        $notifications = $query->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->route('notifications.index');
    }

    public function markAllAsRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return redirect()->back();
    }

    public function unreadCount(Request $request): \Illuminate\Http\JsonResponse
    {
        $count = $request->user()->notifications()->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }
}
